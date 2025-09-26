<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create some predefined categories
        $predefinedCategories = [
            [
                'name' => 'Technology',
                'description' => 'Latest trends and insights in technology, software development, and digital innovation.',
                'status' => 'active',
            ],
            [
                'name' => 'Business',
                'description' => 'Business strategies, entrepreneurship, and industry insights.',
                'status' => 'active',
            ],
            [
                'name' => 'Design',
                'description' => 'UI/UX design, graphic design, and creative inspiration.',
                'status' => 'active',
            ],
            [
                'name' => 'Marketing',
                'description' => 'Digital marketing strategies, SEO tips, and growth hacking techniques.',
                'status' => 'active',
            ],
            [
                'name' => 'Tutorials',
                'description' => 'Step-by-step guides and how-to articles.',
                'status' => 'active',
            ],
            [
                'name' => 'News',
                'description' => 'Latest news and updates from our company and industry.',
                'status' => 'active',
            ],
            [
                'name' => 'Case Studies',
                'description' => 'Real-world examples and success stories.',
                'status' => 'active',
            ],
            [
                'name' => 'Resources',
                'description' => 'Useful tools, templates, and resources for professionals.',
                'status' => 'active',
            ],
        ];

        foreach ($predefinedCategories as $categoryData) {
            Category::factory()->create($categoryData);
        }

        // Create some additional random categories
        Category::factory(5)->create();
        
        // Create a few inactive categories for testing
        Category::factory(2)->inactive()->create();
    }
}