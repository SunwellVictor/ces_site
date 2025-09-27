<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\DownloadGrant;
use App\Models\File;
use App\Models\Post;
use App\Models\Product;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DemoSeedCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:seed {--user-email=demo@example.com : Email of user to grant downloads to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create demo content: categories, posts, products, and files for quick QA';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🌱 Creating demo content...');

        // Get or create demo user
        $userEmail = $this->option('user-email');
        $user = User::firstOrCreate(
            ['email' => $userEmail],
            [
                'name' => 'Demo User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        // Create categories
        $this->info('📁 Creating categories...');
        $categories = $this->createCategories();

        // Create posts
        $this->info('📝 Creating posts...');
        $posts = $this->createPosts($categories, $user);

        // Create products with files
        $this->info('🛍️ Creating products...');
        $products = $this->createProducts();

        // Create sample files and grants
        $this->info('📄 Creating files and grants...');
        $this->createFilesAndGrants($products, $user);

        $this->info('✅ Demo content created successfully!');
        $this->newLine();

        // Print QA URLs
        $this->printQAUrls($categories, $posts, $products);

        return Command::SUCCESS;
    }

    private function createCategories(): array
    {
        $categoryData = [
            [
                'name' => 'English Learning',
                'slug' => 'english-learning',
                'description' => 'Resources and materials for learning English',
                'status' => 'active',
            ],
            [
                'name' => 'Business English',
                'slug' => 'business-english',
                'description' => 'Professional English communication skills',
                'status' => 'active',
            ],
        ];

        $categories = [];
        foreach ($categoryData as $data) {
            $categories[] = Category::firstOrCreate(['slug' => $data['slug']], $data);
        }

        return $categories;
    }

    private function createPosts(array $categories, User $user): array
    {
        $postData = [
            [
                'title' => 'Getting Started with English Learning',
                'slug' => 'getting-started-english-learning',
                'body' => '<p>Welcome to your English learning journey! This comprehensive guide will help you understand the fundamentals of learning English effectively.</p><p>Whether you\'re a complete beginner or looking to improve your existing skills, this post covers essential tips and strategies.</p>',
                'excerpt' => 'A comprehensive guide to starting your English learning journey with practical tips and strategies.',
                'status' => 'published',
                'published_at' => now()->subDays(2),
                'author_id' => $user->id,
                'seo_title' => 'Getting Started with English Learning - Complete Guide',
                'seo_description' => 'Learn English effectively with our comprehensive beginner guide. Discover proven strategies and tips for successful language learning.',
                'category' => $categories[0],
            ],
            [
                'title' => 'Essential Business English Phrases',
                'slug' => 'essential-business-english-phrases',
                'body' => '<p>Master professional communication with these essential business English phrases. Perfect for meetings, emails, and presentations.</p><p>These phrases will help you sound more confident and professional in your workplace communications.</p>',
                'excerpt' => 'Learn key business English phrases for professional communication in meetings, emails, and presentations.',
                'status' => 'published',
                'published_at' => now()->subDays(1),
                'author_id' => $user->id,
                'seo_title' => 'Essential Business English Phrases for Professional Communication',
                'seo_description' => 'Master business English with essential phrases for meetings, emails, and presentations. Improve your professional communication skills.',
                'category' => $categories[1],
            ],
            [
                'title' => 'Advanced Grammar Techniques',
                'slug' => 'advanced-grammar-techniques',
                'body' => '<p>Take your English to the next level with these advanced grammar techniques. This post covers complex structures and nuanced usage.</p>',
                'excerpt' => 'Explore advanced English grammar techniques to elevate your language skills to a professional level.',
                'status' => 'draft',
                'published_at' => null,
                'author_id' => $user->id,
                'seo_title' => 'Advanced English Grammar Techniques',
                'seo_description' => 'Master advanced English grammar with complex structures and professional usage techniques.',
                'category' => $categories[0],
            ],
        ];

        $posts = [];
        foreach ($postData as $data) {
            $category = $data['category'];
            unset($data['category']);
            
            $post = Post::firstOrCreate(['slug' => $data['slug']], $data);
            $post->categories()->syncWithoutDetaching([$category->id]);
            $posts[] = $post;
        }

        return $posts;
    }

    private function createProducts(): array
    {
        $productData = [
            [
                'title' => 'English Learning Starter Pack',
                'slug' => 'english-learning-starter-pack',
                'description' => 'Complete beginner\'s guide to English learning with worksheets, audio files, and practice exercises. Perfect for self-study or classroom use.',
                'price_cents' => 2980, // ¥2,980
                'currency' => 'JPY',
                'is_active' => true,
                'is_digital' => true,
                'seo_title' => 'English Learning Starter Pack - Complete Beginner Guide',
                'seo_description' => 'Download our comprehensive English learning starter pack with worksheets, audio files, and practice exercises for beginners.',
            ],
            [
                'title' => 'Business English Mastery Course',
                'slug' => 'business-english-mastery-course',
                'description' => 'Professional English communication course with real-world scenarios, email templates, and presentation guides. Advance your career with confident English skills.',
                'price_cents' => 4980, // ¥4,980
                'currency' => 'JPY',
                'is_active' => false, // Inactive product for demo
                'is_digital' => true,
                'seo_title' => 'Business English Mastery Course - Professional Communication',
                'seo_description' => 'Master business English with our comprehensive course featuring real scenarios, templates, and professional communication guides.',
            ],
        ];

        $products = [];
        foreach ($productData as $data) {
            $products[] = Product::firstOrCreate(['slug' => $data['slug']], $data);
        }

        return $products;
    }

    private function createFilesAndGrants(array $products, User $user): void
    {
        // Create sample files for the active product
        $activeProduct = $products[0]; // English Learning Starter Pack

        // Create a sample PDF file
        $sampleContent = "# English Learning Starter Pack\n\nWelcome to your English learning journey!\n\nThis is a sample file for demonstration purposes.";
        $fileName = 'english-starter-guide.txt';
        $filePath = 'private/demo/' . $fileName;
        
        Storage::put($filePath, $sampleContent);

        $file = File::firstOrCreate(
            ['path' => $filePath],
            [
                'disk' => 'local',
                'path' => $filePath,
                'original_name' => 'English Starter Guide.txt',
                'size_bytes' => strlen($sampleContent),
                'checksum' => md5($sampleContent),
            ]
        );

        // Associate file with product
        $activeProduct->files()->syncWithoutDetaching([$file->id => ['note' => 'Main guide file']]);

        // Create download grant for the demo user
        DownloadGrant::firstOrCreate(
            [
                'user_id' => $user->id,
                'file_id' => $file->id,
                'product_id' => $activeProduct->id,
            ],
            [
                'expires_at' => now()->addYear(),
                'downloads_used' => 0,
                'max_downloads' => 10,
            ]
        );
    }

    private function printQAUrls(array $categories, array $posts, array $products): void
    {
        $baseUrl = config('app.url');

        $this->info('🔗 QA URLs for testing:');
        $this->newLine();

        // Blog URLs
        $this->line('<fg=cyan>📝 Blog & Posts:</>');
        $this->line("   Blog Index: {$baseUrl}/blog");
        foreach ($categories as $category) {
            $this->line("   Category '{$category->name}': {$baseUrl}/blog/category/{$category->slug}");
        }
        foreach ($posts as $post) {
            if ($post->status === 'published') {
                $this->line("   Post '{$post->title}': {$baseUrl}/blog/{$post->slug}");
            }
        }
        $this->newLine();

        // Product URLs
        $this->line('<fg=cyan>🛍️ Products:</>');
        $this->line("   Products Index: {$baseUrl}/products");
        foreach ($products as $product) {
            $status = $product->is_active ? '✅ Active' : '❌ Inactive';
            $this->line("   Product '{$product->title}' ({$status}): {$baseUrl}/products/{$product->slug}");
        }
        $this->newLine();

        // Account URLs
        $this->line('<fg=cyan>👤 Account Areas:</>');
        $this->line("   Login: {$baseUrl}/login");
        $this->line("   Register: {$baseUrl}/register");
        $this->line("   Account Dashboard: {$baseUrl}/account");
        $this->line("   Downloads: {$baseUrl}/downloads");
        $this->line("   Orders: {$baseUrl}/account/orders");
        $this->newLine();

        // Admin URLs
        $this->line('<fg=cyan>⚙️ Admin Areas:</>');
        $this->line("   Admin Dashboard: {$baseUrl}/admin");
        $this->line("   Admin Posts: {$baseUrl}/admin/posts");
        $this->line("   Admin Products: {$baseUrl}/admin/products");
        $this->line("   Admin Categories: {$baseUrl}/admin/categories");
        $this->newLine();

        // Demo user info
        $this->line('<fg=yellow>👤 Demo User Credentials:</>');
        $this->line("   Email: {$this->option('user-email')}");
        $this->line("   Password: password");
        $this->newLine();

        $this->info('💡 Tip: Login with the demo user to test downloads and account features!');
    }
}
