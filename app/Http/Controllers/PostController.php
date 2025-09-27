<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Services\Meta;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display a listing of published blog posts.
     */
    public function index(Request $request, Meta $meta)
    {
        // Set meta for blog index
        $title = 'Blog - ' . config('app.name');
        $description = 'Read our latest articles and insights on digital products, education, and technology.';
        
        if ($request->has('search')) {
            $title = 'Search Results for "' . $request->search . '" - Blog - ' . config('app.name');
            $description = 'Search results for "' . $request->search . '" in our blog articles.';
        }

        $meta->title($title)
             ->description($description)
             ->canonical(route('blog.index'))
             ->og([
                 'type' => 'website',
                 'title' => $title,
                 'description' => $description,
                 'url' => route('blog.index'),
             ])
             ->twitter([
                 'card' => 'summary',
                 'title' => $title,
                 'description' => $description,
             ]);

        $query = Post::where('status', 'published')
            ->where('published_at', '<=', now())
            ->with(['author', 'categories'])
            ->orderBy('published_at', 'desc');

        // Filter by category if provided
        if ($request->has('category')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('body', 'like', "%{$search}%");
            });
        }

        $posts = $query->paginate(12);
        $categories = Category::orderBy('name')->get();

        return view('blog.index', compact('posts', 'categories'));
    }

    /**
     * Display the specified blog post.
     */
    public function show(string $slug, Meta $meta)
    {
        $post = Post::where('slug', $slug)
            ->where('status', 'published')
            ->where('published_at', '<=', now())
            ->with(['author', 'categories'])
            ->first();
            
        if (!$post) {
            abort(404);
        }

        // Set meta for individual post using SEO fields or fallbacks
        $title = $post->seo_title ?: $post->title;
        $description = $post->seo_description ?: $post->excerpt;
        $canonical = route('blog.show', $post->slug);

        $meta->title($title)
             ->description($description)
             ->canonical($canonical)
             ->og([
                 'type' => 'article',
                 'title' => $title,
                 'description' => $description,
                 'url' => $canonical,
             ])
             ->twitter([
                 'card' => 'summary_large_image',
                 'title' => $title,
                 'description' => $description,
             ]);

        // Get related posts from same categories
        $relatedPosts = Post::where('status', 'published')
            ->where('published_at', '<=', now())
            ->where('id', '!=', $post->id)
            ->whereHas('categories', function ($query) use ($post) {
                $query->whereIn('categories.id', $post->categories->pluck('id'));
            })
            ->limit(3)
            ->get();

        return view('blog.show', compact('post', 'relatedPosts'));
    }

    /**
     * Display posts filtered by category.
     */
    public function category(Category $category, Request $request, Meta $meta)
    {
        // Set meta for category page
        $title = $category->name . ' - Blog - ' . config('app.name');
        $description = 'Read articles about ' . $category->name . '. Browse our collection of posts in this category.';
        $canonical = route('blog.category', $category->slug);

        if ($request->has('search')) {
            $title = 'Search Results for "' . $request->search . '" in ' . $category->name . ' - Blog - ' . config('app.name');
            $description = 'Search results for "' . $request->search . '" in ' . $category->name . ' category.';
        }

        $meta->title($title)
             ->description($description)
             ->canonical($canonical)
             ->og([
                 'type' => 'website',
                 'title' => $title,
                 'description' => $description,
                 'url' => $canonical,
             ])
             ->twitter([
                 'card' => 'summary',
                 'title' => $title,
                 'description' => $description,
             ]);

        $query = Post::where('status', 'published')
            ->where('published_at', '<=', now())
            ->whereHas('categories', function ($q) use ($category) {
                $q->where('categories.id', $category->id);
            })
            ->with(['author', 'categories'])
            ->orderBy('published_at', 'desc');

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('body', 'like', "%{$search}%");
            });
        }

        $posts = $query->paginate(12);
        $categories = Category::orderBy('name')->get();

        return view('blog.category', compact('posts', 'categories', 'category'));
    }
}
