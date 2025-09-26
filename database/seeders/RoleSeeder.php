<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'slug' => 'admin',
                'name' => 'Administrator',
                'display_name' => 'Administrator',
                'description' => 'Full access to all features',
                'permissions' => [
                    'users.create', 'users.read', 'users.update', 'users.delete',
                    'products.create', 'products.read', 'products.update', 'products.delete',
                    'orders.create', 'orders.read', 'orders.update', 'orders.delete',
                    'posts.create', 'posts.read', 'posts.update', 'posts.delete',
                    'files.create', 'files.read', 'files.update', 'files.delete',
                    'roles.create', 'roles.read', 'roles.update', 'roles.delete',
                ],
            ],
            [
                'slug' => 'editor',
                'name' => 'Editor',
                'display_name' => 'Editor',
                'description' => 'Can manage content and posts',
                'permissions' => [
                    'posts.create', 'posts.read', 'posts.update', 'posts.delete',
                    'products.read', 'products.update',
                    'orders.read',
                    'files.read', 'files.update',
                ],
            ],
            [
                'slug' => 'teacher',
                'name' => 'Teacher',
                'display_name' => 'Teacher',
                'description' => 'Can create and manage educational content',
                'permissions' => [
                    'posts.create', 'posts.read', 'posts.update',
                    'products.read',
                    'orders.read',
                    'files.read',
                ],
            ],
            [
                'slug' => 'customer',
                'name' => 'Customer',
                'display_name' => 'Customer',
                'description' => 'Regular customer with purchase access',
                'permissions' => [
                    'products.read',
                    'orders.create', 'orders.read',
                    'posts.read',
                    'files.read',
                ],
            ],
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['slug' => $roleData['slug']],
                $roleData
            );
        }
    }
}
