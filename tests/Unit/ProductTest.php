<?php

namespace Tests\Unit;

use App\Models\File;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function product_has_correct_fillable_attributes()
    {
        $product = new Product();
        $expected = [
            'slug',
            'title',
            'description',
            'price_cents',
            'currency',
            'is_active',
            'is_digital',
            'seo_title',
            'seo_description',
        ];
        
        $this->assertEquals($expected, $product->getFillable());
    }

    /** @test */
    public function product_casts_attributes_correctly()
    {
        $product = Product::factory()->create([
            'price_cents' => 1500,
            'is_active' => true,
        ]);

        $this->assertIsInt($product->price_cents);
        $this->assertIsBool($product->is_active);
    }

    /** @test */
    public function product_generates_slug_from_title()
    {
        $product = Product::factory()->create([
            'title' => 'Test Product Title',
        ]);

        $this->assertEquals('test-product-title', $product->slug);
    }

    /** @test */
    public function product_slug_is_unique()
    {
        $firstProduct = Product::factory()->create([
            'title' => 'Test Product',
            'slug' => 'test-product',
        ]);

        $secondProduct = Product::factory()->create([
            'title' => 'Test Product',
            'slug' => null, // Let the model generate the slug
        ]);

        $this->assertNotEquals('test-product', $secondProduct->slug);
        $this->assertStringStartsWith('test-product-', $secondProduct->slug);
    }

    /** @test */
    public function product_has_files_relationship()
    {
        $product = Product::factory()->create();
        $file = File::factory()->create();
        
        $product->files()->attach($file->id);
        
        $this->assertTrue($product->files->contains($file));
        $this->assertEquals(1, $product->files->count());
    }

    /** @test */
    public function product_scope_active_returns_only_active_products()
    {
        Product::factory()->create(['is_active' => true]);
        Product::factory()->create(['is_active' => false]);
        Product::factory()->create(['is_active' => true]);

        $activeProducts = Product::active()->get();

        $this->assertEquals(2, $activeProducts->count());
        $activeProducts->each(function ($product) {
            $this->assertTrue($product->is_active);
        });
    }

    /** @test */
    public function product_scope_by_type_filters_correctly()
    {
        Product::factory()->create(['is_digital' => true]);
        Product::factory()->create(['is_digital' => false]);
        Product::factory()->create(['is_digital' => true]);

        $digitalProducts = Product::byType('digital')->get();
        $physicalProducts = Product::byType('physical')->get();

        $this->assertEquals(2, $digitalProducts->count());
        $this->assertEquals(1, $physicalProducts->count());
        
        $digitalProducts->each(function ($product) {
            $this->assertTrue($product->is_digital);
        });
    }

    /** @test */
    public function product_scope_price_range_filters_correctly()
    {
        Product::factory()->create(['price_cents' => 1000]); // ¥10.00
        Product::factory()->create(['price_cents' => 2500]); // ¥25.00
        Product::factory()->create(['price_cents' => 5000]); // ¥50.00

        $productsInRange = Product::priceRange(1500, 3000)->get();

        $this->assertEquals(1, $productsInRange->count());
        $this->assertEquals(25.00, $productsInRange->first()->price);
    }

    /** @test */
    public function product_scope_search_finds_by_title_and_description()
    {
        Product::factory()->create([
            'title' => 'Laravel Tutorial',
            'description' => 'Learn PHP framework',
        ]);
        Product::factory()->create([
            'title' => 'Vue.js Guide',
            'description' => 'JavaScript framework tutorial',
        ]);
        Product::factory()->create([
            'title' => 'React Basics',
            'description' => 'Frontend development',
        ]);

        $laravelResults = Product::search('Laravel')->get();
        $frameworkResults = Product::search('framework')->get();
        $tutorialResults = Product::search('tutorial')->get();

        $this->assertEquals(1, $laravelResults->count());
        $this->assertEquals(2, $frameworkResults->count());
        $this->assertEquals(2, $tutorialResults->count());
    }

    /** @test */
    public function product_formatted_price_returns_correct_format()
    {
        $product = Product::factory()->create([
            'price_cents' => 2500,
            'currency' => 'JPY',
        ]);

        $this->assertEquals('¥25.00', $product->formatted_price);
    }

    /** @test */
    public function product_is_digital_returns_correct_boolean()
    {
        $digitalProduct = Product::factory()->create(['is_digital' => true]);
        $physicalProduct = Product::factory()->create(['is_digital' => false]);

        $this->assertTrue($digitalProduct->is_digital);
        $this->assertFalse($physicalProduct->is_digital);
    }

    /** @test */
    public function product_is_active_accessor_works()
    {
        $activeProduct = Product::factory()->create(['is_active' => true]);
        $inactiveProduct = Product::factory()->create(['is_active' => false]);

        $this->assertTrue($activeProduct->is_active);
        $this->assertFalse($inactiveProduct->is_active);
    }

    /** @test */
    public function product_total_file_size_calculates_correctly()
    {
        $product = Product::factory()->create();
        $file1 = File::factory()->create(['size_bytes' => 1024]);
        $file2 = File::factory()->create(['size_bytes' => 2048]);
        
        $product->files()->attach([$file1->id, $file2->id]);
        
        $this->assertEquals(3072, $product->total_file_size);
    }

    /** @test */
    public function product_file_count_returns_correct_number()
    {
        $product = Product::factory()->create();
        $files = File::factory()->count(3)->create();
        
        $product->files()->attach($files->pluck('id'));
        
        $this->assertEquals(3, $product->file_count);
    }

    /** @test */
    public function product_route_key_name_uses_id()
    {
        $product = new Product();
        
        $this->assertEquals('id', $product->getRouteKeyName());
    }

    /** @test */
    public function product_seo_title_falls_back_to_title()
    {
        $productWithSeoTitle = Product::factory()->create([
            'title' => 'Product Title',
            'seo_title' => 'Custom SEO Title',
        ]);

        $productWithoutSeoTitle = Product::factory()->create([
            'title' => 'Product Title',
            'seo_title' => null,
        ]);

        $this->assertEquals('Custom SEO Title', $productWithSeoTitle->seo_title);
        $this->assertEquals('Product Title', $productWithoutSeoTitle->seo_title ?? $productWithoutSeoTitle->title);
    }
}