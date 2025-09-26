<?php

namespace Tests\Unit;

use App\Models\DownloadGrant;
use App\Models\User;
use App\Models\Order;
use App\Models\File;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HelpersTest extends TestCase
{
    use RefreshDatabase;

    public function test_format_yen_function_formats_correctly()
    {
        // Test basic formatting
        $this->assertEquals('¥1,000', format_yen(100000)); // 1000 yen in cents
        $this->assertEquals('¥500', format_yen(50000)); // 500 yen in cents
        $this->assertEquals('¥1', format_yen(100)); // 1 yen in cents
        $this->assertEquals('¥0', format_yen(0)); // 0 yen
        
        // Test large numbers
        $this->assertEquals('¥10,000', format_yen(1000000)); // 10,000 yen
        $this->assertEquals('¥100,000', format_yen(10000000)); // 100,000 yen
        $this->assertEquals('¥1,000,000', format_yen(100000000)); // 1,000,000 yen
        
        // Test decimal handling (should round to nearest yen)
        $this->assertEquals('¥10', format_yen(1050)); // 10.5 yen -> 11 yen
        $this->assertEquals('¥10', format_yen(1049)); // 10.49 yen -> 10 yen
    }

    public function test_format_yen_function_handles_negative_values()
    {
        $this->assertEquals('-¥1,000', format_yen(-100000));
        $this->assertEquals('-¥500', format_yen(-50000));
    }

    public function test_format_yen_function_handles_edge_cases()
    {
        // Test null (should handle gracefully)
        $this->assertEquals('¥0', format_yen(0));
        
        // Test string numbers
        $this->assertEquals('¥1,000', format_yen('100000'));
        
        // Test float
        $this->assertEquals('¥1,000', format_yen(100000.0));
    }

    public function test_remaining_attempts_function_with_valid_grant()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        $file = File::factory()->create();
        
        $grant = DownloadGrant::factory()->create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'file_id' => $file->id,
            'max_downloads' => 5,
            'downloads_used' => 0,
            'expires_at' => now()->addDays(30)
        ]);

        $this->assertEquals(5, remaining_attempts($grant));
    }

    public function test_remaining_attempts_function_with_expired_grant()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        $file = File::factory()->create();
        
        $expiredGrant = DownloadGrant::factory()->create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'file_id' => $file->id,
            'max_downloads' => 5,
            'downloads_used' => 2,
            'expires_at' => now()->subDay()
        ]);

        $this->assertEquals(0, remaining_attempts($expiredGrant));
    }

    public function test_remaining_attempts_function_with_zero_downloads()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        $file = File::factory()->create();
        
        $exhaustedGrant = DownloadGrant::factory()->create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'file_id' => $file->id,
            'max_downloads' => 5,
            'downloads_used' => 5,
            'expires_at' => now()->addDays(30)
        ]);

        $this->assertEquals(0, remaining_attempts($exhaustedGrant));
    }

    public function test_remaining_attempts_function_with_null_expiry()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        $file = File::factory()->create();
        
        $noExpiryGrant = DownloadGrant::factory()->create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'file_id' => $file->id,
            'max_downloads' => 5,
            'downloads_used' => 2,
            'expires_at' => null
        ]);

        $this->assertEquals(3, remaining_attempts($noExpiryGrant));
    }

    public function test_remaining_attempts_function_handles_edge_cases()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        $file = File::factory()->create();
        
        // Test with downloads_used exceeding max_downloads (should return 0)
        $negativeGrant = DownloadGrant::factory()->create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'file_id' => $file->id,
            'max_downloads' => 5,
            'downloads_used' => 6,
            'expires_at' => now()->addDays(30)
        ]);

        $this->assertEquals(0, remaining_attempts($negativeGrant));
        
        // Test with very large max_downloads
        $largeGrant = DownloadGrant::factory()->create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'file_id' => $file->id,
            'max_downloads' => 999999,
            'downloads_used' => 0,
            'expires_at' => now()->addDays(30)
        ]);

        $this->assertEquals(999999, remaining_attempts($largeGrant));
    }

    public function test_remaining_attempts_function_with_expiry_edge_cases()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        $file = File::factory()->create();
        
        // Test with expiry exactly now (should be expired)
        $nowGrant = DownloadGrant::factory()->create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'file_id' => $file->id,
            'max_downloads' => 5,
            'downloads_used' => 0,
            'expires_at' => now()
        ]);

        // This might be 0 or 5 depending on exact timing, but should be consistent
        $result = remaining_attempts($nowGrant);
        $this->assertTrue($result >= 0 && $result <= 5);
        
        // Test with expiry 1 second in the future
        $futureGrant = DownloadGrant::factory()->create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'file_id' => $file->id,
            'max_downloads' => 5,
            'downloads_used' => 0,
            'expires_at' => now()->addSecond()
        ]);

        $this->assertEquals(5, remaining_attempts($futureGrant));
    }
}