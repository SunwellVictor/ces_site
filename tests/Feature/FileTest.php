<?php

namespace Tests\Feature;

use App\Models\File;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::fake('local');
    }

    /** @test */
    public function admin_can_view_file_index()
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(1);

        $file = File::factory()->create(['original_name' => 'test-file.pdf']);

        $response = $this->actingAs($admin)->get('/admin/files');

        $response->assertStatus(200);
        $response->assertSee('test-file.pdf');
    }

    /** @test */
    public function admin_can_upload_file()
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(1);

        $uploadedFile = UploadedFile::fake()->create('document.pdf', 1024);

        $response = $this->actingAs($admin)->post('/admin/files', [
            'file' => $uploadedFile,
            'disk' => 'public',
        ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('files', [
            'original_name' => 'document.pdf',
            'disk' => 'public',
        ]);

        $this->assertTrue(Storage::disk('public')->exists('files/' . $uploadedFile->hashName()));
    }

    /** @test */
    public function admin_can_upload_file_to_local_disk()
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(1);

        $uploadedFile = UploadedFile::fake()->create('private-document.pdf', 2048);

        $response = $this->actingAs($admin)->post('/admin/files', [
            'file' => $uploadedFile,
            'disk' => 'local',
        ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('files', [
            'original_name' => 'private-document.pdf',
            'disk' => 'local',
        ]);

        $this->assertTrue(Storage::disk('local')->exists('files/' . $uploadedFile->hashName()));
    }

    /** @test */
    public function file_upload_generates_checksum()
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(1);

        $uploadedFile = UploadedFile::fake()->create('test.txt', 100);

        $response = $this->actingAs($admin)->post('/admin/files', [
            'file' => $uploadedFile,
            'disk' => 'public',
        ]);

        $file = File::where('original_name', 'test.txt')->first();
        
        $this->assertNotNull($file->checksum);
        $this->assertEquals(64, strlen($file->checksum)); // SHA256 length
    }

    /** @test */
    public function file_upload_stores_correct_metadata()
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(1);

        $uploadedFile = UploadedFile::fake()->create('metadata-test.pdf', 1500);

        $response = $this->actingAs($admin)->post('/admin/files', [
            'file' => $uploadedFile,
            'disk' => 'public',
        ]);

        $file = File::where('original_name', 'metadata-test.pdf')->first();
        
        $this->assertEquals('metadata-test.pdf', $file->original_name);
        $this->assertEquals('public', $file->disk);
        $this->assertEquals(1500 * 1024, $file->size_bytes); // Convert KB to bytes
        $this->assertStringStartsWith('files/', $file->path);
    }

    /** @test */
    public function admin_can_delete_file()
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(1);

        $file = File::factory()->create([
            'disk' => 'public',
            'path' => 'files/test-file.pdf',
        ]);

        // Create the actual file in storage
        Storage::disk('public')->put($file->path, 'test content');

        $response = $this->actingAs($admin)->delete("/admin/files/{$file->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('files', ['id' => $file->id]);
        $this->assertFalse(Storage::disk('public')->exists($file->path));
    }

    /** @test */
    public function admin_can_download_file()
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(1);

        $file = File::factory()->create([
            'disk' => 'public',
            'path' => 'files/download-test.pdf',
            'original_name' => 'download-test.pdf',
        ]);

        // Create the actual file in storage
        Storage::disk('public')->put($file->path, 'test file content');

        $response = $this->actingAs($admin)->get("/admin/files/{$file->id}/download");

        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition', 'attachment; filename="download-test.pdf"');
    }

    /** @test */
    public function file_index_can_be_searched()
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(1);

        File::factory()->create(['original_name' => 'laravel-guide.pdf']);
        File::factory()->create(['original_name' => 'vue-tutorial.pdf']);

        $response = $this->actingAs($admin)->get('/admin/files?search=laravel');

        $response->assertStatus(200);
        $response->assertSee('laravel-guide.pdf');
        $response->assertDontSee('vue-tutorial.pdf');
    }

    /** @test */
    public function file_index_can_be_filtered_by_size()
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(1);

        File::factory()->create([
            'original_name' => 'small-file.txt',
            'size_bytes' => 1024,
        ]);
        File::factory()->create([
            'original_name' => 'large-file.pdf',
            'size_bytes' => 10485760, // 10MB
        ]);

        $response = $this->actingAs($admin)->get('/admin/files?min_size=5&max_size=15');

        $response->assertStatus(200);
        $response->assertSee('large-file.pdf');
        $response->assertDontSee('small-file.txt');
    }

    /** @test */
    public function non_admin_cannot_access_file_management()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin/files');
        $response->assertStatus(403);

        $response = $this->actingAs($user)->post('/admin/files', []);
        $response->assertStatus(403);
    }

    /** @test */
    public function file_upload_validates_required_fields()
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(1);

        $response = $this->actingAs($admin)->post('/admin/files', [
            'disk' => 'public',
            // Missing 'file' field
        ]);

        $response->assertSessionHasErrors(['file']);
    }

    /** @test */
    public function file_upload_validates_disk_option()
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(1);

        $uploadedFile = UploadedFile::fake()->create('test.pdf', 100);

        $response = $this->actingAs($admin)->post('/admin/files', [
            'file' => $uploadedFile,
            'disk' => 'invalid-disk',
        ]);

        $response->assertSessionHasErrors(['disk']);
    }

    /** @test */
    public function file_shows_associated_products()
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(1);

        $file = File::factory()->create(['original_name' => 'shared-file.pdf']);
        $product1 = Product::factory()->create(['title' => 'Product One']);
        $product2 = Product::factory()->create(['title' => 'Product Two']);

        $file->products()->attach([$product1->id, $product2->id]);

        $response = $this->actingAs($admin)->get('/admin/files');

        $response->assertStatus(200);
        $response->assertSee('Product One');
        $response->assertSee('Product Two');
    }

    /** @test */
    public function file_storage_summary_shows_correct_stats()
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(1);

        File::factory()->create(['size_bytes' => 1048576]); // 1MB
        File::factory()->create(['size_bytes' => 2097152]); // 2MB

        $response = $this->actingAs($admin)->get('/admin/files');

        $response->assertStatus(200);
        $response->assertSee('3.0 MB'); // Total size
        $response->assertSee('2'); // Total files
    }
}
