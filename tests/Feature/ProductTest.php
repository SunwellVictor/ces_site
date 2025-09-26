<?php

namespace Tests\Feature;

use App\Models\File;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::fake('local');
    }

    /** @test */
    public function guests_can_view_product_catalog()
    {
        $product = Product::factory()->create([
            'is_active' => true,
            'title' => 'Test Product',
            'price_cents' => 1000,
        ]);

        $response = $this->get('/products');

        $response->assertStatus(200);
        $response->assertSee('Test Product');
        $response->assertSee('¥1,000');
    }

    /** @test */
    public function guests_can_view_individual_product()
    {
        $product = Product::factory()->create([
            'is_active' => true,
            'title' => 'Test Product',
            'slug' => 'test-product',
            'description' => 'This is a test product',
            'price_cents' => 1500,
        ]);

        $response = $this->get("/products/{$product->slug}");

        $response->assertStatus(200);
        $response->assertSee('Test Product');
        $response->assertSee('This is a test product');
        $response->assertSee('¥1,500');
    }

    /** @test */
    public function guests_cannot_view_inactive_products()
    {
        $product = Product::factory()->create([
            'is_active' => false,
            'slug' => 'inactive-product',
        ]);

        $response = $this->get("/products/{$product->slug}");

        $response->assertStatus(404);
    }

    /** @test */
    public function product_catalog_can_be_searched()
    {
        Product::factory()->create([
            'is_active' => true,
            'title' => 'Laravel Course',
        ]);
        Product::factory()->create([
            'is_active' => true,
            'title' => 'Vue.js Tutorial',
        ]);

        $response = $this->get('/products?search=Laravel');

        $response->assertStatus(200);
        $response->assertSee('Laravel Course');
        $response->assertDontSee('Vue.js Tutorial');
    }

    /** @test */
    public function product_catalog_can_be_filtered_by_type()
    {
        Product::factory()->create([
            'is_active' => true,
            'title' => 'Digital Product',
            'is_digital' => true,
        ]);
        Product::factory()->create([
            'is_active' => true,
            'title' => 'Physical Product',
            'is_digital' => false,
        ]);

        $response = $this->get('/products?type=digital');

        $response->assertStatus(200);
        $response->assertSee('Digital Product');
        $response->assertDontSee('Physical Product');
    }

    /** @test */
    public function product_catalog_can_be_filtered_by_price_range()
    {
        Product::factory()->create([
            'is_active' => true,
            'title' => 'Cheap Product',
            'price_cents' => 500,
        ]);
        Product::factory()->create([
            'is_active' => true,
            'title' => 'Expensive Product',
            'price_cents' => 5000,
        ]);

        $response = $this->get('/products?min_price=1000&max_price=10000');

        $response->assertStatus(200);
        $response->assertSee('Expensive Product');
        $response->assertDontSee('Cheap Product');
    }

    /** @test */
    public function admin_can_view_product_index()
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(1); // Assuming role ID 1 is admin

        $product = Product::factory()->create(['title' => 'Admin Product']);

        $response = $this->actingAs($admin)->get('/admin/products');

        $response->assertStatus(200);
        $response->assertSee('Admin Product');
    }

    /** @test */
    public function admin_can_create_product()
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(1);

        $productData = [
            'title' => 'New Product',
            'description' => 'Product description',
            'price_cents' => 2000,
            'currency' => 'JPY',
            'is_digital' => true,
            'is_active' => true,
        ];

        $response = $this->actingAs($admin)->post('/admin/products', $productData);

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'title' => 'New Product',
            'price_cents' => 2000,
        ]);
    }

    /** @test */
    public function admin_can_update_product()
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(1);

        $product = Product::factory()->create(['title' => 'Original Title']);

        $updateData = [
            'title' => 'Updated Title',
            'description' => $product->description,
            'price_cents' => $product->price_cents,
            'currency' => $product->currency,
            'is_active' => $product->is_active,
            'is_digital' => $product->is_digital,
        ];

        $response = $this->actingAs($admin)->put("/admin/products/{$product->id}", $updateData);

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'title' => 'Updated Title',
        ]);
    }

    /** @test */
    public function admin_can_delete_product()
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(1);

        $product = Product::factory()->create();

        $response = $this->actingAs($admin)->delete("/admin/products/{$product->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    /** @test */
    public function admin_can_attach_files_to_product()
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(1);

        $product = Product::factory()->create();
        $file = File::factory()->create();

        $response = $this->actingAs($admin)->post("/admin/products/{$product->id}/files", [
            'file_id' => $file->id,
        ]);

        $response->assertRedirect();
        $this->assertTrue($product->files()->where('file_id', $file->id)->exists());
    }

    /** @test */
    public function admin_can_detach_files_from_product()
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(1);

        $product = Product::factory()->create();
        $file = File::factory()->create();
        $product->files()->attach($file->id);

        $response = $this->actingAs($admin)->delete("/admin/products/{$product->id}/files/{$file->id}");

        $response->assertRedirect();
        $this->assertFalse($product->files()->where('file_id', $file->id)->exists());
    }

    /** @test */
    public function non_admin_cannot_access_admin_product_routes()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin/products');
        $response->assertStatus(403);

        $response = $this->actingAs($user)->post('/admin/products', []);
        $response->assertStatus(403);
    }

    /** @test */
    public function product_shows_related_products()
    {
        $product = Product::factory()->create([
            'is_active' => true,
            'is_digital' => true,
            'slug' => 'main-product',
        ]);

        $relatedProduct = Product::factory()->create([
            'is_active' => true,
            'is_digital' => true,
            'title' => 'Related Product',
        ]);

        $unrelatedProduct = Product::factory()->create([
            'is_active' => true,
            'is_digital' => false,
            'title' => 'Unrelated Product',
        ]);

        $response = $this->get("/products/{$product->slug}");

        $response->assertStatus(200);
        $response->assertSee('Related Product');
        $response->assertDontSee('Unrelated Product');
    }
}
