<?php

namespace Tests\Unit;

use App\Models\DownloadGrant;
use App\Models\DownloadToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DownloadTokenTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function download_token_has_correct_fillable_attributes()
    {
        $token = new DownloadToken();
        
        $expected = [
            'grant_id',
            'token',
            'expires_at',
            'used_at',
        ];
        
        $this->assertEquals($expected, $token->getFillable());
    }

    /** @test */
    public function download_token_casts_attributes_correctly()
    {
        $token = DownloadToken::factory()->create([
            'expires_at' => now()->addMinutes(15),
            'used_at' => now(),
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $token->expires_at);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $token->used_at);
    }

    /** @test */
    public function download_token_belongs_to_download_grant()
    {
        $grant = DownloadGrant::factory()->create();
        $token = DownloadToken::factory()->create(['grant_id' => $grant->id]);

        $this->assertInstanceOf(DownloadGrant::class, $token->downloadGrant);
        $this->assertEquals($grant->id, $token->downloadGrant->id);
    }

    /** @test */
    public function download_token_is_valid_when_not_expired_and_not_used()
    {
        $token = DownloadToken::factory()->create([
            'expires_at' => now()->addMinutes(15),
            'used_at' => null,
        ]);

        $this->assertTrue($token->isValid());
    }

    /** @test */
    public function download_token_is_invalid_when_expired()
    {
        $token = DownloadToken::factory()->create([
            'expires_at' => now()->subMinutes(1),
            'used_at' => null,
        ]);

        $this->assertFalse($token->isValid());
    }

    /** @test */
    public function download_token_is_invalid_when_used()
    {
        $token = DownloadToken::factory()->create([
            'expires_at' => now()->addMinutes(15),
            'used_at' => now()->subMinutes(5),
        ]);

        $this->assertFalse($token->isValid());
    }

    /** @test */
    public function download_token_is_invalid_when_both_expired_and_used()
    {
        $token = DownloadToken::factory()->create([
            'expires_at' => now()->subMinutes(1),
            'used_at' => now()->subMinutes(5),
        ]);

        $this->assertFalse($token->isValid());
    }

    /** @test */
    public function download_token_can_be_marked_as_used()
    {
        $token = DownloadToken::factory()->create(['used_at' => null]);

        $this->assertNull($token->used_at);
        $this->assertTrue($token->isValid());

        $token->markAsUsed();

        $this->assertNotNull($token->used_at);
        $this->assertFalse($token->isValid());
    }

    /** @test */
    public function download_token_mark_as_used_updates_database()
    {
        $token = DownloadToken::factory()->create(['used_at' => null]);

        $token->markAsUsed();

        $this->assertDatabaseHas('download_tokens', [
            'id' => $token->id,
            'used_at' => $token->used_at,
        ]);
    }

    /** @test */
    public function download_token_is_expired_returns_correct_boolean()
    {
        $expiredToken = DownloadToken::factory()->create([
            'expires_at' => now()->subMinutes(1),
        ]);

        $validToken = DownloadToken::factory()->create([
            'expires_at' => now()->addMinutes(15),
        ]);

        $this->assertTrue($expiredToken->isExpired());
        $this->assertFalse($validToken->isExpired());
    }

    /** @test */
    public function download_token_is_used_returns_correct_boolean()
    {
        $usedToken = DownloadToken::factory()->create([
            'used_at' => now()->subMinutes(5),
        ]);

        $unusedToken = DownloadToken::factory()->create([
            'used_at' => null,
        ]);

        $this->assertTrue($usedToken->isUsed());
        $this->assertFalse($unusedToken->isUsed());
    }

    /** @test */
    public function download_token_minutes_until_expiry_calculates_correctly()
    {
        $token = DownloadToken::factory()->create([
            'expires_at' => now()->addMinutes(10),
        ]);

        $minutesUntilExpiry = $token->minutesUntilExpiry();

        // Allow for small timing differences in test execution
        $this->assertGreaterThanOrEqual(9, $minutesUntilExpiry);
        $this->assertLessThanOrEqual(10, $minutesUntilExpiry);
    }

    /** @test */
    public function download_token_minutes_until_expiry_returns_zero_when_expired()
    {
        $token = DownloadToken::factory()->create([
            'expires_at' => now()->subMinutes(5),
        ]);

        $this->assertEquals(0, $token->minutesUntilExpiry());
    }

    /** @test */
    public function download_token_scope_valid_returns_only_valid_tokens()
    {
        // Valid token
        DownloadToken::factory()->create([
            'expires_at' => now()->addMinutes(15),
            'used_at' => null,
        ]);

        // Expired token
        DownloadToken::factory()->create([
            'expires_at' => now()->subMinutes(1),
            'used_at' => null,
        ]);

        // Used token
        DownloadToken::factory()->create([
            'expires_at' => now()->addMinutes(15),
            'used_at' => now()->subMinutes(5),
        ]);

        $validTokens = DownloadToken::valid()->get();

        $this->assertEquals(1, $validTokens->count());
    }

    /** @test */
    public function download_token_scope_for_grant_filters_correctly()
    {
        $grant1 = DownloadGrant::factory()->create();
        $grant2 = DownloadGrant::factory()->create();

        DownloadToken::factory()->create(['grant_id' => $grant1->id]);
        DownloadToken::factory()->create(['grant_id' => $grant2->id]);
        DownloadToken::factory()->create(['grant_id' => $grant1->id]);

        $grant1Tokens = DownloadToken::forGrant($grant1->id)->get();
        $grant2Tokens = DownloadToken::forGrant($grant2->id)->get();

        $this->assertEquals(2, $grant1Tokens->count());
        $this->assertEquals(1, $grant2Tokens->count());
    }

    /** @test */
    public function download_token_scope_unused_returns_only_unused_tokens()
    {
        DownloadToken::factory()->create(['used_at' => null]);
        DownloadToken::factory()->create(['used_at' => now()->subMinutes(5)]);
        DownloadToken::factory()->create(['used_at' => null]);

        $unusedTokens = DownloadToken::unused()->get();

        $this->assertEquals(2, $unusedTokens->count());
        $unusedTokens->each(function ($token) {
            $this->assertNull($token->used_at);
        });
    }

    /** @test */
    public function download_token_scope_expired_returns_only_expired_tokens()
    {
        DownloadToken::factory()->create(['expires_at' => now()->addMinutes(15)]);
        DownloadToken::factory()->create(['expires_at' => now()->subMinutes(1)]);
        DownloadToken::factory()->create(['expires_at' => now()->subMinutes(5)]);

        $expiredTokens = DownloadToken::expired()->get();

        $this->assertEquals(2, $expiredTokens->count());
        $expiredTokens->each(function ($token) {
            $this->assertTrue($token->expires_at->isPast());
        });
    }

    /** @test */
    public function download_token_generates_unique_token_string()
    {
        $token1 = DownloadToken::factory()->create();
        $token2 = DownloadToken::factory()->create();

        $this->assertNotEquals($token1->token, $token2->token);
        $this->assertIsString($token1->token);
        $this->assertIsString($token2->token);
        $this->assertGreaterThan(10, strlen($token1->token)); // Should be reasonably long
    }

    /** @test */
    public function download_token_timestamps_are_recorded()
    {
        $token = DownloadToken::factory()->create();

        $this->assertNotNull($token->created_at);
        $this->assertNotNull($token->updated_at);
    }
}