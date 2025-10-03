<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles for testing
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
    }

    /**
     * Test that guests are redirected to login when accessing admin area.
     */
    public function test_guest_redirected_to_login_when_accessing_admin(): void
    {
        $response = $this->get('/admin');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /**
     * Test that non-admin users get 403 when accessing admin area.
     */
    public function test_non_admin_user_gets_403_when_accessing_admin(): void
    {
        $user = User::factory()->create();
        
        // Assign customer role (non-admin)
        $customerRole = Role::where('slug', 'customer')->first();
        $user->roles()->attach($customerRole->id);

        $response = $this->actingAs($user)->get('/admin');

        $response->assertStatus(403);
    }

    /**
     * Test that admin users can access admin area.
     */
    public function test_admin_user_can_access_admin(): void
    {
        $user = User::factory()->create();
        
        // Assign admin role
        $adminRole = Role::where('slug', 'admin')->first();
        $user->roles()->attach($adminRole->id);

        $response = $this->actingAs($user)->get('/admin');

        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
    }
}
