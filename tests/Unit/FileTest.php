<?php

namespace Tests\Unit;

use App\Models\File;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FileTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function file_has_correct_fillable_attributes()
    {
        $file = new File();
        
        $expected = [
            'disk',
            'path',
            'original_name',
            'size_bytes',
            'checksum',
        ];
        
        $this->assertEquals($expected, $file->getFillable());
    }

    /** @test */
    public function file_casts_attributes_correctly()
    {
        $file = File::factory()->create([
            'size_bytes' => 1024,
        ]);

        $this->assertIsInt($file->size_bytes);
    }

    /** @test */
    public function file_has_products_relationship()
    {
        $file = File::factory()->create();
        $product = Product::factory()->create();
        
        $file->products()->attach($product->id);
        
        $this->assertTrue($file->products->contains($product));
        $this->assertEquals(1, $file->products->count());
    }

    /** @test */
    public function file_has_download_grants_relationship()
    {
        $file = File::factory()->create();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $file->downloadGrants());
    }

    /** @test */
    public function file_can_be_attached_to_product_with_pivot_data()
    {
        $file = File::factory()->create();
        $product = Product::factory()->create();
        
        $file->products()->attach($product->id, ['note' => 'Main documentation file']);
        
        $attachedProduct = $file->products->first();
        $this->assertEquals('Main documentation file', $attachedProduct->pivot->note);
    }

    /** @test */
    public function file_stores_checksum_correctly()
    {
        $checksum = hash('sha256', 'test content');
        
        $file = File::factory()->create([
            'checksum' => $checksum,
        ]);
        
        $this->assertEquals($checksum, $file->checksum);
        $this->assertEquals(64, strlen($file->checksum)); // SHA256 is 64 characters
    }

    /** @test */
    public function file_stores_size_in_bytes()
    {
        $file = File::factory()->create([
            'size_bytes' => 2048,
        ]);
        
        $this->assertEquals(2048, $file->size_bytes);
        $this->assertIsInt($file->size_bytes);
    }

    /** @test */
    public function file_stores_disk_and_path_correctly()
    {
        $file = File::factory()->create([
            'disk' => 'public',
            'path' => 'files/documents/test.pdf',
        ]);
        
        $this->assertEquals('public', $file->disk);
        $this->assertEquals('files/documents/test.pdf', $file->path);
    }

    /** @test */
    public function file_stores_original_name()
    {
        $file = File::factory()->create([
            'original_name' => 'My Important Document.pdf',
        ]);
        
        $this->assertEquals('My Important Document.pdf', $file->original_name);
    }

    /** @test */
    public function multiple_products_can_use_same_file()
    {
        $file = File::factory()->create();
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();
        
        $file->products()->attach([
            $product1->id => ['note' => 'Primary file'],
            $product2->id => ['note' => 'Secondary file'],
        ]);
        
        $this->assertEquals(2, $file->products->count());
        $this->assertTrue($file->products->contains($product1));
        $this->assertTrue($file->products->contains($product2));
    }

    /** @test */
    public function file_can_be_detached_from_product()
    {
        $file = File::factory()->create();
        $product = Product::factory()->create();
        
        $file->products()->attach($product->id);
        $this->assertEquals(1, $file->products->count());
        
        $file->products()->detach($product->id);
        $file->refresh();
        $this->assertEquals(0, $file->products->count());
    }

    /** @test */
    public function file_timestamps_are_recorded()
    {
        $file = File::factory()->create();
        
        $this->assertNotNull($file->created_at);
        $this->assertNotNull($file->updated_at);
    }
}