<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;

class PromoteUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:promote {email} {role}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Promote a user to a specific role (idempotent)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $roleName = $this->argument('role');

        // Find the user
        $user = User::where('email', $email)->first();
        if (!$user) {
            $this->error("User with email '{$email}' not found.");
            return 1;
        }

        // Find the role
        $role = Role::where('slug', $roleName)->first();
        if (!$role) {
            $this->error("Role '{$roleName}' not found.");
            return 1;
        }

        // Check if user already has this role (idempotent)
        if ($user->hasRole($roleName)) {
            $this->info("User '{$email}' already has the '{$roleName}' role.");
            return 0;
        }

        // Assign the role
        $user->roles()->attach($role->id);

        $this->info("Successfully promoted user '{$email}' to '{$roleName}' role.");
        return 0;
    }
}
