<?php

namespace Tests\Unit;

use App\Models\DownloadGrant;
use App\Models\DownloadToken;
use App\Models\File;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DownloadGrantTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function download_grant_has_correct_fillable_attributes()
    {
        $grant = new DownloadGrant();
        
        $expected = [
            'user_id',
            'product_id',
            'file_id',
            'order_id',
            'max_downloads',
            'downloads_used',
            'expires_at',
        ];
        
        $this->assertEquals($expected, $grant->getFillable());
    }

    /** @test */
    public function download_grant_casts_attributes_correctly()
    {
        $grant = DownloadGrant::factory()->create([
            'max_downloads' => 5,
            'downloads_used' => 2,
            'expires_at' => now()->addDays(30),
        ]);

        $this->assertIsInt($grant->max_downloads);
        $this->assertIsInt($grant->downloads_used);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $grant->expires_at);
    }

    /** @test */
    public function download_grant_belongs_to_user()
    {
        $user = User::factory()->create();
        $grant = DownloadGrant::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $grant->user);
        $this->assertEquals($user->id, $grant->user->id);
    }

    /** @test */
    public function download_grant_belongs_to_product()
    {
        $product = Product::factory()->create();
        $grant = DownloadGrant::factory()->create(['product_id' => $product->id]);

        $this->assertInstanceOf(Product::class, $grant->product);
        $this->assertEquals($product->id, $grant->product->id);
    }

    /** @test */
    public function download_grant_belongs_to_file()
    {
        $file = File::factory()->create();
        $grant = DownloadGrant::factory()->create(['file_id' => $file->id]);

        $this->assertInstanceOf(File::class, $grant->file);
        $this->assertEquals($file->id, $grant->file->id);
    }

    /** @test */
    public function download_grant_has_many_download_tokens()
    {
        $grant = DownloadGrant::factory()->create();
        $token1 = DownloadToken::factory()->create(['grant_id' => $grant->id]);
        $token2 = DownloadToken::factory()->create(['grant_id' => $grant->id]);

        $this->assertEquals(2, $grant->downloadTokens->count());
        $this->assertTrue($grant->downloadTokens->contains($token1));
        $this->assertTrue($grant->downloadTokens->contains($token2));
    }

    /** @test */
    public function download_grant_is_valid_when_not_expired_and_has_downloads_remaining()
    {
        $grant = DownloadGrant::factory()->create([
            'max_downloads' => 5,
            'downloads_used' => 2,
            'expires_at' => now()->addDays(30),
        ]);

        $this->assertTrue($grant->isValid());
    }

    /** @test */
    public function download_grant_is_invalid_when_expired()
    {
        $grant = DownloadGrant::factory()->create([
            'max_downloads' => 5,
            'downloads_used' => 2,
            'expires_at' => now()->subDays(1),
        ]);

        $this->assertFalse($grant->isValid());
    }

    /** @test */
    public function download_grant_is_invalid_when_downloads_exhausted()
    {
        $grant = DownloadGrant::factory()->create([
            'max_downloads' => 3,
            'downloads_used' => 3,
            'expires_at' => now()->addDays(30),
        ]);

        $this->assertFalse($grant->isValid());
    }

    /** @test */
    public function download_grant_downloads_remaining_calculates_correctly()
    {
        $grant = DownloadGrant::factory()->create([
            'max_downloads' => 10,
            'downloads_used' => 3,
        ]);

        $this->assertEquals(7, $grant->downloadsRemaining());
    }

    /** @test */
    public function download_grant_downloads_remaining_returns_zero_when_exhausted()
    {
        $grant = DownloadGrant::factory()->create([
            'max_downloads' => 5,
            'downloads_used' => 5,
        ]);

        $this->assertEquals(0, $grant->downloadsRemaining());
    }

    /** @test */
    public function download_grant_downloads_remaining_returns_zero_when_over_limit()
    {
        $grant = DownloadGrant::factory()->create([
            'max_downloads' => 5,
            'downloads_used' => 7, // Somehow exceeded
        ]);

        $this->assertEquals(0, $grant->downloadsRemaining());
    }

    /** @test */
    public function download_grant_days_until_expiry_calculates_correctly()
    {
        $grant = DownloadGrant::factory()->create([
            'expires_at' => now()->addDays(15),
        ]);

        $this->assertEquals(15, $grant->daysUntilExpiry());
    }

    /** @test */
    public function download_grant_days_until_expiry_returns_zero_when_expired()
    {
        $grant = DownloadGrant::factory()->create([
            'expires_at' => now()->subDays(5),
        ]);

        $this->assertEquals(0, $grant->daysUntilExpiry());
    }

    /** @test */
    public function download_grant_can_increment_downloads_used()
    {
        $grant = DownloadGrant::factory()->create([
            'downloads_used' => 2,
        ]);

        $grant->incrementDownloadsUsed();

        $this->assertEquals(3, $grant->downloads_used);
    }

    /** @test */
    public function download_grant_scope_valid_returns_only_valid_grants()
    {
        // Valid grant
        DownloadGrant::factory()->create([
            'max_downloads' => 5,
            'downloads_used' => 2,
            'expires_at' => now()->addDays(30),
        ]);

        // Expired grant
        DownloadGrant::factory()->create([
            'max_downloads' => 5,
            'downloads_used' => 2,
            'expires_at' => now()->subDays(1),
        ]);

        // Exhausted grant
        DownloadGrant::factory()->create([
            'max_downloads' => 3,
            'downloads_used' => 3,
            'expires_at' => now()->addDays(30),
        ]);

        $validGrants = DownloadGrant::valid()->get();

        $this->assertEquals(1, $validGrants->count());
    }

    /** @test */
    public function download_grant_scope_for_user_filters_correctly()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        DownloadGrant::factory()->create(['user_id' => $user1->id]);
        DownloadGrant::factory()->create(['user_id' => $user2->id]);
        DownloadGrant::factory()->create(['user_id' => $user1->id]);

        $user1Grants = DownloadGrant::forUser($user1->id)->get();
        $user2Grants = DownloadGrant::forUser($user2->id)->get();

        $this->assertEquals(2, $user1Grants->count());
        $this->assertEquals(1, $user2Grants->count());
    }

    /** @test */
    public function download_grant_scope_for_product_filters_correctly()
    {
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        DownloadGrant::factory()->create(['product_id' => $product1->id]);
        DownloadGrant::factory()->create(['product_id' => $product2->id]);
        DownloadGrant::factory()->create(['product_id' => $product1->id]);

        $product1Grants = DownloadGrant::forProduct($product1->id)->get();
        $product2Grants = DownloadGrant::forProduct($product2->id)->get();

        $this->assertEquals(2, $product1Grants->count());
        $this->assertEquals(1, $product2Grants->count());
    }

    /** @test */
    public function download_grant_timestamps_are_recorded()
    {
        $grant = DownloadGrant::factory()->create();

        $this->assertNotNull($grant->created_at);
        $this->assertNotNull($grant->updated_at);
    }
}