<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure we have users and categories
        $users = User::all();
        if ($users->isEmpty()) {
            $users = User::factory(3)->create();
        }

        $categories = Category::where('status', 'active')->get();
        if ($categories->isEmpty()) {
            $categories = Category::factory(5)->active()->create();
        }

        // Create some featured posts with specific content
        $featuredPosts = [
            [
                'title' => 'Getting Started with Laravel 11: A Complete Guide',
                'excerpt' => 'Learn the fundamentals of Laravel 11 and build your first web application with this comprehensive tutorial.',
                'status' => 'published',
            ],
            [
                'title' => 'The Future of Web Development: Trends to Watch in 2024',
                'excerpt' => 'Explore the latest trends shaping the future of web development, from AI integration to new frameworks.',
                'status' => 'published',
            ],
            [
                'title' => 'Building Scalable APIs with Laravel',
                'excerpt' => 'Best practices for designing and implementing scalable REST APIs using Laravel framework.',
                'status' => 'published',
            ],
            [
                'title' => 'UI/UX Design Principles for Modern Web Applications',
                'excerpt' => 'Essential design principles every developer should know when creating user-friendly web applications.',
                'status' => 'published',
            ],
            [
                'title' => 'Database Optimization Techniques for High-Performance Applications',
                'excerpt' => 'Learn advanced database optimization strategies to improve your application performance.',
                'status' => 'published',
            ],
        ];

        foreach ($featuredPosts as $postData) {
            $post = Post::factory()
                ->published()
                ->byAuthor($users->random())
                ->create($postData);

            // Attach 1-3 random categories to each post
            $post->categories()->attach(
                $categories->random(rand(1, 3))->pluck('id')->toArray()
            );
        }

        // Create published posts
        $publishedPosts = Post::factory(25)
            ->published()
            ->create([
                'author_id' => fn() => $users->random()->id,
            ]);

        // Attach categories to published posts
        foreach ($publishedPosts as $post) {
            $post->categories()->attach(
                $categories->random(rand(1, 4))->pluck('id')->toArray()
            );
        }

        // Create draft posts
        $draftPosts = Post::factory(8)
            ->draft()
            ->create([
                'author_id' => fn() => $users->random()->id,
            ]);

        // Attach categories to draft posts
        foreach ($draftPosts as $post) {
            $post->categories()->attach(
                $categories->random(rand(1, 2))->pluck('id')->toArray()
            );
        }

        // Create some posts without categories for testing
        Post::factory(3)
            ->published()
            ->create([
                'author_id' => fn() => $users->random()->id,
            ]);

        // Create posts with specific publication dates for testing
        Post::factory(5)
            ->publishedOn(now()->subDays(30))
            ->create([
                'author_id' => fn() => $users->random()->id,
            ])
            ->each(function ($post) use ($categories) {
                $post->categories()->attach(
                    $categories->random(rand(1, 2))->pluck('id')->toArray()
                );
            });
    }
}