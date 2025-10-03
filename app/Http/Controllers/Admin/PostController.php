<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    /**
     * Display a listing of posts with search and filtering.
     */
    public function index(Request $request)
    {
        $query = Post::with(['category', 'user']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Category filter
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Featured filter
        if ($request->filled('featured')) {
            $query->where('featured', $request->boolean('featured'));
        }

        $posts = $query->latest()->paginate(20);
        $categories = Category::where('status', 'active')->get();

        return view('admin.posts.index', compact('posts', 'categories'));
    }

    /**
     * Show the form for creating a new post.
     */
    public function create()
    {
        $categories = Category::where('status', 'active')->get();
        return view('admin.posts.create', compact('categories'));
    }

    /**
     * Store a newly created post in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'category_id' => ['required', 'exists:categories,id'],
            'status' => ['required', 'in:draft,published,archived'],
            'featured' => ['boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'published_at' => ['nullable', 'date'],
        ]);

        $validated['slug'] = Str::slug($validated['title']);
        $validated['user_id'] = Auth::id();
        $validated['featured'] = $request->boolean('featured');

        // Set published_at if status is published and no date provided
        if ($validated['status'] === 'published' && !$validated['published_at']) {
            $validated['published_at'] = now();
        }

        $post = Post::create($validated);

        return redirect()->route('admin.posts.index')
                        ->with('success', 'Post created successfully.');
    }

    /**
     * Display the specified post.
     */
    public function show(Post $post)
    {
        $post->load(['category', 'user']);
        return view('admin.posts.show', compact('post'));
    }

    /**
     * Show the form for editing the specified post.
     */
    public function edit(Post $post)
    {
        $categories = Category::where('status', 'active')->get();
        return view('admin.posts.edit', compact('post', 'categories'));
    }

    /**
     * Update the specified post in storage.
     */
    public function update(Request $request, Post $post)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'category_id' => ['required', 'exists:categories,id'],
            'status' => ['required', 'in:draft,published,archived'],
            'featured' => ['boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'published_at' => ['nullable', 'date'],
        ]);

        $validated['slug'] = Str::slug($validated['title']);
        $validated['featured'] = $request->boolean('featured');

        // Set published_at if status is published and no date provided
        if ($validated['status'] === 'published' && !$validated['published_at'] && $post->status !== 'published') {
            $validated['published_at'] = now();
        }

        $post->update($validated);

        return redirect()->route('admin.posts.index')
                        ->with('success', 'Post updated successfully.');
    }

    /**
     * Remove the specified post from storage.
     */
    public function destroy(Post $post)
    {
        $post->delete();

        return redirect()->route('admin.posts.index')
                        ->with('success', 'Post deleted successfully.');
    }

    /**
     * Duplicate the specified post.
     */
    public function duplicate(Post $post)
    {
        $newPost = $post->replicate();
        $newPost->title = $post->title . ' (Copy)';
        $newPost->slug = Str::slug($newPost->title);
        $newPost->status = 'draft';
        $newPost->published_at = null;
        $newPost->user_id = Auth::id();
        $newPost->save();

        return redirect()->route('admin.posts.edit', $newPost)
                        ->with('success', 'Post duplicated successfully.');
    }

    /**
     * Publish the specified post.
     */
    public function publish(Post $post)
    {
        if ($post->status === 'published') {
            return redirect()->route('admin.posts.show', $post)
                            ->with('error', 'Post is already published.');
        }

        $post->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        return redirect()->route('admin.posts.show', $post)
                        ->with('success', 'Post published successfully.');
    }

    /**
     * Unpublish the specified post.
     */
    public function unpublish(Post $post)
    {
        if ($post->status !== 'published') {
            return redirect()->route('admin.posts.show', $post)
                            ->with('error', 'Post is not published.');
        }

        $post->update([
            'status' => 'draft',
        ]);

        return redirect()->route('admin.posts.show', $post)
                        ->with('success', 'Post unpublished successfully.');
    }

    /**
     * Toggle featured status of the specified post.
     */
    public function toggleFeatured(Post $post)
    {
        $post->update([
            'featured' => !$post->featured,
        ]);

        $status = $post->featured ? 'featured' : 'unfeatured';

        return redirect()->route('admin.posts.show', $post)
                        ->with('success', "Post {$status} successfully.");
    }
}
