<?php

namespace Tests\Feature;

use App\Models\DownloadGrant;
use App\Models\DownloadToken;
use App\Models\File;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DownloadGrantTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::fake('local');
    }

    /** @test */
    public function user_can_view_their_download_grants()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['title' => 'Test Product']);
        $file = File::factory()->create(['original_name' => 'test-file.pdf']);

        DownloadGrant::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'file_id' => $file->id,
            'max_downloads' => 5,
            'downloads_used' => 2,
            'expires_at' => now()->addDays(30),
        ]);

        $response = $this->actingAs($user)->get('/downloads');

        $response->assertStatus(200);
        $response->assertSee('Test Product');
        $response->assertSee('test-file.pdf');
        $response->assertSee('3 remaining'); // 5 - 2 = 3
    }

    /** @test */
    public function user_can_issue_download_token()
    {
        $user = User::factory()->create();
        $grant = DownloadGrant::factory()->create([
            'user_id' => $user->id,
            'max_downloads' => 5,
            'downloads_used' => 2,
            'expires_at' => now()->addDays(30),
        ]);

        $response = $this->actingAs($user)->post("/downloads/{$grant->id}/token");

        $response->assertStatus(200);
        $responseData = $response->json();
        
        $this->assertArrayHasKey('token', $responseData);
        $this->assertArrayHasKey('expires_at', $responseData);
        
        $this->assertDatabaseHas('download_tokens', [
            'grant_id' => $grant->id,
            'token' => $responseData['token'],
        ]);
    }

    /** @test */
    public function user_cannot_issue_token_for_expired_grant()
    {
        $user = User::factory()->create();
        $grant = DownloadGrant::factory()->create([
            'user_id' => $user->id,
            'expires_at' => now()->subDays(1), // Expired
        ]);

        $response = $this->actingAs($user)->post("/downloads/{$grant->id}/token");

        $response->assertStatus(403);
        $response->assertJson(['error' => 'Grant is no longer valid']);
    }

    /** @test */
    public function user_cannot_issue_token_for_exhausted_grant()
    {
        $user = User::factory()->create();
        $grant = DownloadGrant::factory()->create([
            'user_id' => $user->id,
            'max_downloads' => 3,
            'downloads_used' => 3, // Exhausted
            'expires_at' => now()->addDays(30),
        ]);

        $response = $this->actingAs($user)->post("/downloads/{$grant->id}/token");

        $response->assertStatus(403);
        $response->assertJson(['error' => 'Grant is no longer valid']);
    }

    /** @test */
    public function user_cannot_issue_token_for_other_users_grant()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $grant = DownloadGrant::factory()->create([
            'user_id' => $otherUser->id,
            'expires_at' => now()->addDays(30),
        ]);

        $response = $this->actingAs($user)->post("/downloads/{$grant->id}/token");

        $response->assertStatus(403);
    }

    /** @test */
    public function user_can_download_file_with_valid_token()
    {
        $user = User::factory()->create();
        $file = File::factory()->create([
            'disk' => 'public',
            'path' => 'files/download-test.pdf',
            'original_name' => 'download-test.pdf',
        ]);
        $grant = DownloadGrant::factory()->create([
            'user_id' => $user->id,
            'file_id' => $file->id,
            'max_downloads' => 5,
            'downloads_used' => 2,
            'expires_at' => now()->addDays(30),
        ]);

        // Create the actual file in storage
        Storage::disk('public')->put($file->path, 'test file content');

        $token = DownloadToken::factory()->create([
            'grant_id' => $grant->id,
            'expires_at' => now()->addMinutes(15),
            'used_at' => null,
        ]);

        $response = $this->get("/download/{$token->token}");

        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition', 'attachment; filename="download-test.pdf"');
        
        // Check that download was recorded
        $grant->refresh();
        $this->assertEquals(3, $grant->downloads_used);
        
        // Check that token was marked as used
        $token->refresh();
        $this->assertNotNull($token->used_at);
    }

    /** @test */
    public function cannot_download_with_expired_token()
    {
        $token = DownloadToken::factory()->create([
            'expires_at' => now()->subMinutes(1), // Expired
            'used_at' => null,
        ]);

        $response = $this->get("/download/{$token->token}");

        $response->assertStatus(403);
        $response->assertJson(['error' => 'Token is invalid or expired']);
    }

    /** @test */
    public function cannot_download_with_used_token()
    {
        $token = DownloadToken::factory()->create([
            'expires_at' => now()->addMinutes(15),
            'used_at' => now()->subMinutes(5), // Already used
        ]);

        $response = $this->get("/download/{$token->token}");

        $response->assertStatus(403);
        $response->assertJson(['error' => 'Token is invalid or expired']);
    }

    /** @test */
    public function user_can_view_download_stats()
    {
        $user = User::factory()->create();
        
        DownloadGrant::factory()->create([
            'user_id' => $user->id,
            'downloads_used' => 3,
        ]);
        DownloadGrant::factory()->create([
            'user_id' => $user->id,
            'downloads_used' => 2,
        ]);

        $response = $this->actingAs($user)->get('/downloads/stats');

        $response->assertStatus(200);
        $responseData = $response->json();
        
        $this->assertEquals(2, $responseData['total_grants']);
        $this->assertEquals(5, $responseData['total_downloads']);
    }

    /** @test */
    public function user_can_check_grant_status()
    {
        $user = User::factory()->create();
        $grant = DownloadGrant::factory()->create([
            'user_id' => $user->id,
            'max_downloads' => 10,
            'downloads_used' => 3,
            'expires_at' => now()->addDays(15),
        ]);

        $response = $this->actingAs($user)->get("/downloads/grants/{$grant->id}/status");

        $response->assertStatus(200);
        $responseData = $response->json();
        
        $this->assertTrue($responseData['is_valid']);
        $this->assertEquals(7, $responseData['downloads_remaining']);
        $this->assertEquals(15, $responseData['days_until_expiry']);
    }

    /** @test */
    public function download_grant_model_validates_correctly()
    {
        // Valid grant
        $validGrant = DownloadGrant::factory()->create([
            'max_downloads' => 5,
            'downloads_used' => 2,
            'expires_at' => now()->addDays(30),
        ]);
        $this->assertTrue($validGrant->isValid());

        // Expired grant
        $expiredGrant = DownloadGrant::factory()->create([
            'max_downloads' => 5,
            'downloads_used' => 2,
            'expires_at' => now()->subDays(1),
        ]);
        $this->assertFalse($expiredGrant->isValid());

        // Exhausted grant
        $exhaustedGrant = DownloadGrant::factory()->create([
            'max_downloads' => 3,
            'downloads_used' => 3,
            'expires_at' => now()->addDays(30),
        ]);
        $this->assertFalse($exhaustedGrant->isValid());
    }

    /** @test */
    public function download_token_model_validates_correctly()
    {
        // Valid token
        $validToken = DownloadToken::factory()->create([
            'expires_at' => now()->addMinutes(15),
            'used_at' => null,
        ]);
        $this->assertTrue($validToken->isValid());

        // Expired token
        $expiredToken = DownloadToken::factory()->create([
            'expires_at' => now()->subMinutes(1),
            'used_at' => null,
        ]);
        $this->assertFalse($expiredToken->isValid());

        // Used token
        $usedToken = DownloadToken::factory()->create([
            'expires_at' => now()->addMinutes(15),
            'used_at' => now()->subMinutes(5),
        ]);
        $this->assertFalse($usedToken->isValid());
    }

    /** @test */
    public function download_token_can_be_marked_as_used()
    {
        $token = DownloadToken::factory()->create(['used_at' => null]);
        
        $this->assertNull($token->used_at);
        
        $token->markAsUsed();
        
        $this->assertNotNull($token->used_at);
        $this->assertFalse($token->isValid());
    }
}
