<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\File;
use App\Models\Order;
use App\Models\DownloadGrant;
use App\Models\DownloadToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DownloadControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $otherUser;
    protected $product;
    protected $file;
    protected $order;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Disable CSRF middleware for all tests in this class
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
        
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
        $this->file = File::factory()->create([
            'disk' => 'local',
            'original_name' => 'test-file.pdf',
            'path' => 'files/test-file.pdf',
            'size_bytes' => 1024
        ]);
        $this->product = Product::factory()->create([
            'price_cents' => 1000,
            'is_active' => true
        ]);
        $this->product->files()->attach($this->file);
        
        $this->order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'paid'
        ]);
    }

    public function test_guest_cannot_access_downloads_index()
    {
        $response = $this->get(route('downloads.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_user_can_view_downloads_index()
    {
        DownloadGrant::factory()->create([
            'user_id' => $this->user->id,
            'order_id' => $this->order->id,
            'file_id' => $this->file->id
        ]);

        $response = $this->actingAs($this->user)->get(route('downloads.index'));
        
        $response->assertStatus(200);
        $response->assertViewIs('downloads.index');
        $response->assertSee($this->file->filename);
    }

    public function test_downloads_index_only_shows_user_grants()
    {
        // Create grant for current user
        $userGrant = DownloadGrant::factory()->create([
            'user_id' => $this->user->id,
            'order_id' => $this->order->id,
            'file_id' => $this->file->id
        ]);

        // Create grant for other user
        $otherOrder = Order::factory()->create(['user_id' => $this->otherUser->id]);
        $otherFile = File::factory()->create(['original_name' => 'other-file.pdf']);
        $otherGrant = DownloadGrant::factory()->create([
            'user_id' => $this->otherUser->id,
            'order_id' => $otherOrder->id,
            'file_id' => $otherFile->id
        ]);

        $response = $this->actingAs($this->user)->get(route('downloads.index'));
        
        $response->assertStatus(200);
        $response->assertSee($this->file->filename);
        $response->assertDontSee($otherFile->filename);
    }

    public function test_downloads_index_pagination_works()
    {
        // Create more than 10 download grants to test pagination
        DownloadGrant::factory()->count(15)->create([
            'user_id' => $this->user->id,
            'order_id' => $this->order->id
        ]);

        $response = $this->actingAs($this->user)->get(route('downloads.index'));
        
        $response->assertStatus(200);
        $response->assertViewHas('downloadGrants');
        
        // Check that pagination is working
        $grants = $response->viewData('downloadGrants');
        $this->assertEquals(10, $grants->perPage());
        $this->assertEquals(15, $grants->total());
    }

    public function test_user_can_issue_download_token_for_own_grant()
    {
        $grant = DownloadGrant::factory()->create([
            'user_id' => $this->user->id,
            'order_id' => $this->order->id,
            'file_id' => $this->file->id,
            'max_downloads' => 5,
            'downloads_used' => 0
        ]);

        $response = $this->actingAs($this->user)
            ->withSession(['_token' => 'test-token'])
            ->post(route('downloads.token', $grant), [
                'grant_id' => $grant->id,
                '_token' => 'test-token'
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['token', 'expires_at']);
        
        // Verify token was created in database
        $this->assertDatabaseHas('download_tokens', [
            'grant_id' => $grant->id
        ]);
    }

    public function test_user_cannot_issue_token_for_other_users_grant()
    {
        $otherOrder = Order::factory()->create(['user_id' => $this->otherUser->id]);
        $otherGrant = DownloadGrant::factory()->create([
            'user_id' => $this->otherUser->id,
            'order_id' => $otherOrder->id,
            'file_id' => $this->file->id
        ]);

        $response = $this->actingAs($this->user)
            ->withSession(['_token' => 'test-token'])
            ->post(route('downloads.token', $otherGrant), [
                'grant_id' => $otherGrant->id,
                '_token' => 'test-token'
            ]);

        $response->assertStatus(403);
    }

    public function test_cannot_issue_token_for_expired_grant()
    {
        $expiredGrant = DownloadGrant::factory()->create([
            'user_id' => $this->user->id,
            'order_id' => $this->order->id,
            'file_id' => $this->file->id,
            'expires_at' => now()->subDay()
        ]);

        $response = $this->actingAs($this->user)
            ->withSession(['_token' => 'test-token'])
            ->post(route('downloads.token', $expiredGrant), [
                'grant_id' => $expiredGrant->id,
                '_token' => 'test-token'
            ]);

        $response->assertStatus(403);
        $response->assertJson(['error' => 'Grant is no longer valid']);
    }

    public function test_cannot_issue_token_when_no_downloads_remaining()
    {
        $grant = DownloadGrant::factory()->create([
            'user_id' => $this->user->id,
            'order_id' => $this->order->id,
            'file_id' => $this->file->id,
            'max_downloads' => 5,
            'downloads_used' => 5
        ]);

        $response = $this->actingAs($this->user)
            ->withSession(['_token' => 'test-token'])
            ->post(route('downloads.token', $grant), [
                'grant_id' => $grant->id,
                '_token' => 'test-token'
            ]);

        $response->assertStatus(403);
        $response->assertJson(['error' => 'Grant is no longer valid']);
    }

    public function test_token_issuance_rate_limiting()
    {
        $grant = DownloadGrant::factory()->create([
            'user_id' => $this->user->id,
            'order_id' => $this->order->id,
            'file_id' => $this->file->id,
            'max_downloads' => 10,
            'downloads_used' => 0
        ]);

        // Make 5 requests (the limit) - our custom rate limiting allows 5 per minute
        for ($i = 0; $i < 5; $i++) {
            $response = $this->actingAs($this->user)
                ->withSession(['_token' => 'test-token'])
                ->post(route('downloads.token', $grant), [
                    'grant_id' => $grant->id,
                    '_token' => 'test-token'
                ]);
            $response->assertStatus(200);
        }

        // 6th request should be rate limited by our custom rate limiting
        $response = $this->actingAs($this->user)
            ->withSession(['_token' => 'test-token'])
            ->post(route('downloads.token', $grant), [
                'grant_id' => $grant->id,
                '_token' => 'test-token'
            ]);
        
        $response->assertStatus(429); // Too Many Requests
    }

    public function test_download_token_consumption_works()
    {
        Storage::fake('local');
        // Create a minimal PDF content that will be detected as PDF
        $pdfContent = "%PDF-1.4\n1 0 obj\n<<\n/Type /Catalog\n/Pages 2 0 R\n>>\nendobj\n2 0 obj\n<<\n/Type /Pages\n/Kids [3 0 R]\n/Count 1\n>>\nendobj\n3 0 obj\n<<\n/Type /Page\n/Parent 2 0 R\n/MediaBox [0 0 612 792]\n>>\nendobj\nxref\n0 4\n0000000000 65535 f \n0000000009 00000 n \n0000000058 00000 n \n0000000115 00000 n \ntrailer\n<<\n/Size 4\n/Root 1 0 R\n>>\nstartxref\n174\n%%EOF";
        Storage::disk('local')->put('files/test-file.pdf', $pdfContent);

        $grant = DownloadGrant::factory()->create([
            'user_id' => $this->user->id,
            'order_id' => $this->order->id,
            'file_id' => $this->file->id,
            'max_downloads' => 5,
            'downloads_used' => 0
        ]);

        $token = DownloadToken::factory()->create([
            'grant_id' => $grant->id,
            'token' => 'test-token-123',
            'expires_at' => now()->addMinutes(15)
        ]);

        $response = $this->get(route('downloads.consume', ['token' => 'test-token-123']));
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('Content-Disposition', 'attachment; filename="test-file.pdf"');
        
        // Verify token was marked as used
        $this->assertDatabaseHas('download_tokens', [
            'token' => 'test-token-123'
        ]);
        
        $token->refresh();
        $this->assertNotNull($token->used_at);
        
        // Verify downloads remaining was decremented
        $grant->refresh();
        $this->assertEquals(4, $grant->downloadsRemaining());
    }

    public function test_cannot_consume_expired_token()
    {
        $grant = DownloadGrant::factory()->create([
            'user_id' => $this->user->id,
            'order_id' => $this->order->id,
            'file_id' => $this->file->id
        ]);

        $expiredToken = DownloadToken::factory()->create([
            'grant_id' => $grant->id,
            'token' => 'expired-token',
            'expires_at' => now()->subMinute()
        ]);

        $response = $this->get(route('downloads.consume', ['token' => 'expired-token']));
        
        $response->assertStatus(403);
    }

    public function test_cannot_consume_invalid_token()
    {
        $response = $this->get(route('downloads.consume', ['token' => 'invalid-token']));
        
        $response->assertStatus(404);
    }

    public function test_download_stats_endpoint_works()
    {
        DownloadGrant::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'order_id' => $this->order->id
        ]);

        $response = $this->actingAs($this->user)->get(route('downloads.stats'));
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total_grants',
            'active_grants',
            'expired_grants',
            'total_downloads'
        ]);
    }

    public function test_grant_status_check_endpoint_works()
    {
        $grant = DownloadGrant::factory()->create([
            'user_id' => $this->user->id,
            'order_id' => $this->order->id,
            'file_id' => $this->file->id,
            'max_downloads' => 5,
            'downloads_used' => 2
        ]);

        $response = $this->actingAs($this->user)->get(route('downloads.grant.status', $grant));
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'is_valid',
            'downloads_used',
            'max_downloads',
            'downloads_remaining',
            'expires_at',
            'days_until_expiry'
        ]);
    }

    public function test_user_cannot_check_other_users_grant_status()
    {
        $otherOrder = Order::factory()->create(['user_id' => $this->otherUser->id]);
        $otherGrant = DownloadGrant::factory()->create([
            'user_id' => $this->otherUser->id,
            'order_id' => $otherOrder->id,
            'file_id' => $this->file->id
        ]);

        $response = $this->actingAs($this->user)->get(route('downloads.grant.status', $otherGrant));
        
        $response->assertStatus(403);
    }

    public function test_successful_token_issuance_returns_correct_structure()
    {
        $grant = DownloadGrant::factory()->create([
            'user_id' => $this->user->id,
            'order_id' => $this->order->id,
            'file_id' => $this->file->id,
            'max_downloads' => 5,
            'downloads_used' => 0
        ]);

        $response = $this->actingAs($this->user)
            ->withSession(['_token' => 'test-token'])
            ->post(route('downloads.token', $grant), [
                'grant_id' => $grant->id,
                '_token' => 'test-token'
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'token',
            'download_url',
            'expires_at',
            'message'
        ]);
    }

    public function test_remaining_attempts_helper_function()
    {
        $grant = DownloadGrant::factory()->create([
            'user_id' => $this->user->id,
            'order_id' => $this->order->id,
            'file_id' => $this->file->id,
            'max_downloads' => 5,
            'downloads_used' => 2,
            'expires_at' => now()->addDays(7)
        ]);

        $remaining = remaining_attempts($grant);
        $this->assertEquals(3, $remaining);

        // Test expired grant
        $expiredGrant = DownloadGrant::factory()->create([
            'user_id' => $this->user->id,
            'order_id' => $this->order->id,
            'file_id' => $this->file->id,
            'max_downloads' => 5,
            'downloads_used' => 2,
            'expires_at' => now()->subDay()
        ]);

        $remaining = remaining_attempts($expiredGrant);
        $this->assertEquals(0, $remaining);
    }
}