<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BlogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding blog data...');
        
        // Seed categories first
        $this->command->info('Creating categories...');
        $this->call(CategorySeeder::class);
        
        // Then seed posts with category relationships
        $this->command->info('Creating posts...');
        $this->call(PostSeeder::class);
        
        $this->command->info('Blog seeding completed!');
    }
}