<?php

namespace Tests\Unit;

use App\Console\Commands\PromoteUserCommand;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromoteUserCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles for testing
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
    }

    /**
     * Test that command assigns role to user successfully.
     */
    public function test_command_assigns_role_to_user(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        
        $this->artisan('user:promote', ['email' => 'test@example.com', 'role' => 'admin'])
            ->expectsOutput("Successfully promoted user 'test@example.com' to 'admin' role.")
            ->assertExitCode(0);

        $this->assertTrue($user->fresh()->hasRole('admin'));
    }

    /**
     * Test that command is idempotent (safe to run multiple times).
     */
    public function test_command_is_idempotent(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        $adminRole = Role::where('slug', 'admin')->first();
        
        // Assign role manually first
        $user->roles()->attach($adminRole->id);
        
        $this->artisan('user:promote', ['email' => 'test@example.com', 'role' => 'admin'])
            ->expectsOutput("User 'test@example.com' already has the 'admin' role.")
            ->assertExitCode(0);

        // Should still have only one role relationship
        $this->assertEquals(1, $user->fresh()->roles()->count());
    }

    /**
     * Test that command fails gracefully for non-existent user.
     */
    public function test_command_fails_for_non_existent_user(): void
    {
        $this->artisan('user:promote', ['email' => 'nonexistent@example.com', 'role' => 'admin'])
            ->expectsOutput("User with email 'nonexistent@example.com' not found.")
            ->assertExitCode(1);
    }

    /**
     * Test that command fails gracefully for non-existent role.
     */
    public function test_command_fails_for_non_existent_role(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        
        $this->artisan('user:promote', ['email' => 'test@example.com', 'role' => 'nonexistent'])
            ->expectsOutput("Role 'nonexistent' not found.")
            ->assertExitCode(1);
    }
}
