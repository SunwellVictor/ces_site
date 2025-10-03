<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that registration triggers email verification.
     */
    public function test_registration_triggers_email_verification(): void
    {
        Event::fake();

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        Event::assertDispatched(Registered::class);
        
        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNull($user->email_verified_at);
    }

    /**
     * Test that unverified users cannot access account area.
     */
    public function test_unverified_user_cannot_access_account(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/account');

        $response->assertRedirect('/verify-email');
    }

    /**
     * Test that verified users can access account area.
     */
    public function test_verified_user_can_access_account(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/account');

        $response->assertStatus(200);
        $response->assertViewIs('account.dashboard');
    }
}
