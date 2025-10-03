<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogVisibilityTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function guests_can_view_published_posts_in_blog_index()
    {
        $publishedPost = Post::factory()->published()->create([
            'title' => 'Published Post',
        ]);
        
        $draftPost = Post::factory()->draft()->create([
            'title' => 'Draft Post',
        ]);

        $response = $this->get('/blog');

        $response->assertStatus(200);
        $response->assertSee('Published Post');
        $response->assertDontSee('Draft Post');
    }

    /** @test */
    public function guests_can_view_individual_published_posts()
    {
        $post = Post::factory()->published()->create([
            'title' => 'Published Post',
            'slug' => 'published-post',
            'body' => 'This is the content of a published post.',
        ]);

        $response = $this->get("/blog/{$post->slug}");

        $response->assertStatus(200);
        $response->assertSee('Published Post');
        $response->assertSee('This is the content of a published post.');
    }

    /** @test */
    public function guests_cannot_view_draft_posts()
    {
        $post = Post::factory()->draft()->create([
            'slug' => 'draft-post',
        ]);

        $response = $this->get("/blog/{$post->slug}");

        $response->assertStatus(404);
    }

    /** @test */
    public function guests_cannot_view_future_published_posts()
    {
        $post = Post::factory()->create([
            'status' => 'published',
            'published_at' => now()->addDay(),
            'slug' => 'future-post',
        ]);

        $response = $this->get("/blog/{$post->slug}");

        $response->assertStatus(404);
    }

    /** @test */
    public function blog_index_only_shows_published_posts()
    {
        // Create various post states
        Post::factory()->published()->create(['title' => 'Published 1']);
        Post::factory()->published()->create(['title' => 'Published 2']);
        Post::factory()->draft()->create(['title' => 'Draft 1']);
        Post::factory()->create([
            'status' => 'published',
            'published_at' => now()->addDay(),
            'title' => 'Future Post',
        ]);

        $response = $this->get('/blog');

        $response->assertStatus(200);
        $response->assertSee('Published 1');
        $response->assertSee('Published 2');
        $response->assertDontSee('Draft 1');
        $response->assertDontSee('Future Post');
    }

    /** @test */
    public function blog_search_only_returns_published_posts()
    {
        Post::factory()->published()->create([
            'title' => 'Laravel Tutorial Published',
        ]);
        
        Post::factory()->draft()->create([
            'title' => 'Laravel Tutorial Draft',
        ]);

        $response = $this->get('/blog?search=Laravel');

        $response->assertStatus(200);
        $response->assertSee('Laravel Tutorial Published');
        $response->assertDontSee('Laravel Tutorial Draft');
    }
}