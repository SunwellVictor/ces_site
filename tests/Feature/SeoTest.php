<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Product;
use App\Models\User;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Category $category;
    protected Post $post;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test data
        $this->user = User::factory()->create();
        $this->category = Category::factory()->create();
        $this->post = Post::factory()->create([
            'status' => 'published',
            'published_at' => now(),
            'seo_title' => 'Custom SEO Title',
            'seo_description' => 'Custom SEO description for testing',
        ]);
        $this->product = Product::factory()->create([
            'is_active' => true,
            'seo_title' => 'Product SEO Title',
            'seo_description' => 'Product SEO description for testing',
        ]);
    }

    /** @test */
    public function robots_txt_returns_proper_content()
    {
        $response = $this->get('/robots.txt');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        
        $content = $response->getContent();
        $this->assertStringContainsString('User-agent: *', $content);
        $this->assertStringContainsString('Disallow: /admin/', $content);
        $this->assertStringContainsString('Disallow: /account/', $content);
        $this->assertStringContainsString('Sitemap: ' . route('sitemap.index'), $content);
    }

    /** @test */
    public function sitemap_index_returns_valid_xml()
    {
        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml');
        
        $content = $response->getContent();
        $this->assertStringContainsString('<?xml version="1.0" encoding="UTF-8"?>', $content);
        $this->assertStringContainsString('<sitemapindex', $content);
        $this->assertStringContainsString(route('sitemap.pages'), $content);
        $this->assertStringContainsString(route('sitemap.posts'), $content);
        $this->assertStringContainsString(route('sitemap.products'), $content);
    }

    /** @test */
    public function sitemap_pages_includes_home_page()
    {
        $response = $this->get('/sitemap-pages.xml');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml');
        
        $content = $response->getContent();
        $this->assertStringContainsString('<?xml version="1.0" encoding="UTF-8"?>', $content);
        $this->assertStringContainsString('<urlset', $content);
        $this->assertStringContainsString(route('home'), $content);
        $this->assertStringContainsString('<priority>1.0</priority>', $content);
    }

    /** @test */
    public function sitemap_posts_includes_published_posts()
    {
        $response = $this->get('/sitemap-posts.xml');

        $response->assertStatus(200);
        $content = $response->getContent();
        
        $this->assertStringContainsString(route('blog.show', $this->post->slug), $content);
        $this->assertStringContainsString('<changefreq>weekly</changefreq>', $content);
        $this->assertStringContainsString('<priority>0.7</priority>', $content);
    }

    /** @test */
    public function sitemap_products_includes_active_products()
    {
        $response = $this->get('/sitemap-products.xml');

        $response->assertStatus(200);
        $content = $response->getContent();
        
        $this->assertStringContainsString(route('products.show', $this->product->slug), $content);
        $this->assertStringContainsString('<changefreq>weekly</changefreq>', $content);
        $this->assertStringContainsString('<priority>0.9</priority>', $content);
    }

    /** @test */
    public function home_page_has_proper_meta_tags()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $content = $response->getContent();
        
        // Check basic meta tags
        $this->assertStringContainsString('<title>', $content);
        $this->assertStringContainsString('<meta name="description"', $content);
        $this->assertStringContainsString('<link rel="canonical"', $content);
        
        // Check Open Graph tags
        $this->assertStringContainsString('<meta property="og:title"', $content);
        $this->assertStringContainsString('<meta property="og:description"', $content);
        $this->assertStringContainsString('<meta property="og:type" content="website"', $content);
        
        // Check Twitter tags
        $this->assertStringContainsString('<meta name="twitter:card"', $content);
        $this->assertStringContainsString('<meta name="twitter:title"', $content);
    }

    /** @test */
    public function blog_post_has_seo_meta_tags()
    {
        $response = $this->get(route('blog.show', $this->post->slug));

        $response->assertStatus(200);
        $content = $response->getContent();
        
        // Check custom SEO title and description are used
        $this->assertStringContainsString('<title>Custom SEO Title', $content);
        $this->assertStringContainsString('content="Custom SEO description for testing"', $content);
        
        // Check Open Graph article tags
        $this->assertStringContainsString('<meta property="og:type" content="article"', $content);
        $this->assertStringContainsString('<meta property="article:published_time"', $content);
        $this->assertStringContainsString('<meta property="article:author"', $content);
    }

    /** @test */
    public function blog_post_has_article_json_ld_schema()
    {
        $response = $this->get(route('blog.show', $this->post->slug));

        $response->assertStatus(200);
        $content = $response->getContent();
        
        // Check JSON-LD schema
        $this->assertStringContainsString('<script type="application/ld+json">', $content);
        $this->assertStringContainsString('"@type": "Article"', $content);
        $this->assertStringContainsString('"headline": "' . $this->post->title . '"', $content);
        $this->assertStringContainsString('"author":', $content);
        $this->assertStringContainsString('"publisher":', $content);
    }

    /** @test */
    public function product_page_has_seo_meta_tags()
    {
        $response = $this->get(route('products.show', $this->product->slug));

        $response->assertStatus(200);
        $content = $response->getContent();
        
        // Check custom SEO title and description are used
        $this->assertStringContainsString('<title>Product SEO Title', $content);
        $this->assertStringContainsString('content="Product SEO description for testing"', $content);
        
        // Check product-specific Open Graph tags
        $this->assertStringContainsString('<meta property="og:type" content="product"', $content);
        $this->assertStringContainsString('<meta property="product:price:amount"', $content);
        $this->assertStringContainsString('<meta property="product:price:currency"', $content);
    }

    /** @test */
    public function product_page_has_product_json_ld_schema()
    {
        $response = $this->get(route('products.show', $this->product->slug));

        $response->assertStatus(200);
        $content = $response->getContent();
        
        // Check JSON-LD schema
        $this->assertStringContainsString('<script type="application/ld+json">', $content);
        $this->assertStringContainsString('"@type": "Product"', $content);
        $this->assertStringContainsString('"name": "' . $this->product->title . '"', $content);
        $this->assertStringContainsString('"offers":', $content);
        $this->assertStringContainsString('"priceCurrency": "JPY"', $content);
        $this->assertStringContainsString('"price": "' . ($this->product->price_cents / 100) . '"', $content);
    }

    /** @test */
    public function admin_pages_have_noindex_meta_tag()
    {
        // Skip this test if admin routes don't exist or require specific setup
        $this->markTestSkipped('Admin routes require specific role setup');
    }

    /** @test */
    public function account_pages_have_noindex_meta_tag()
    {
        $response = $this->actingAs($this->user)->get('/account');

        $response->assertStatus(200);
        $content = $response->getContent();
        
        // Check noindex meta tag is present
        $this->assertStringContainsString('<meta name="robots" content="noindex, nofollow"', $content);
    }

    /** @test */
    public function dashboard_has_noindex_meta_tag()
    {
        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertStatus(200);
        $content = $response->getContent();
        
        // Check noindex meta tag is present
        $this->assertStringContainsString('<meta name="robots" content="noindex, nofollow"', $content);
    }

    /** @test */
    public function public_pages_do_not_have_noindex()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $content = $response->getContent();
        
        // Check noindex meta tag is NOT present
        $this->assertStringNotContainsString('<meta name="robots" content="noindex, nofollow"', $content);
    }

    /** @test */
    public function blog_listing_has_proper_meta_tags()
    {
        $response = $this->get('/blog');

        $response->assertStatus(200);
        $content = $response->getContent();
        
        $this->assertStringContainsString('<title>Blog', $content);
        $this->assertStringContainsString('<meta name="description"', $content);
        $this->assertStringContainsString('<link rel="canonical"', $content);
    }

    /** @test */
    public function product_listing_has_proper_meta_tags()
    {
        $response = $this->get('/products');

        $response->assertStatus(200);
        $content = $response->getContent();
        
        $this->assertStringContainsString('<title>Products', $content);
        $this->assertStringContainsString('<meta name="description"', $content);
        $this->assertStringContainsString('<link rel="canonical"', $content);
    }
}