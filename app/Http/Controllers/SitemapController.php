<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Generate robots.txt
     */
    public function robots()
    {
        $content = view('seo.robots')->render();

        return response($content, 200, [
            'Content-Type' => 'text/plain',
        ]);
    }

    /**
     * Generate the main sitemap index
     */
    public function index()
    {
        $sitemaps = [
            [
                'loc' => route('sitemap.pages'),
                'lastmod' => now()->toISOString(),
            ],
            [
                'loc' => route('sitemap.posts'),
                'lastmod' => Post::where('status', 'published')->latest('updated_at')->first()?->updated_at?->toISOString() ?? now()->toISOString(),
            ],
            [
                'loc' => route('sitemap.products'),
                'lastmod' => Product::where('is_active', true)->latest('updated_at')->first()?->updated_at?->toISOString() ?? now()->toISOString(),
            ],
        ];

        $xml = view('sitemaps.index', compact('sitemaps'))->render();

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }

    /**
     * Generate sitemap for static pages
     */
    public function pages()
    {
        $urls = [
            [
                'loc' => route('home'),
                'lastmod' => now()->toISOString(),
                'changefreq' => 'daily',
                'priority' => '1.0',
            ],
            [
                'loc' => route('blog.index'),
                'lastmod' => now()->toISOString(),
                'changefreq' => 'daily',
                'priority' => '0.8',
            ],
            [
                'loc' => route('products.index'),
                'lastmod' => now()->toISOString(),
                'changefreq' => 'daily',
                'priority' => '0.8',
            ],
        ];

        $xml = view('sitemaps.urlset', compact('urls'))->render();

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }

    /**
     * Generate sitemap for blog posts
     */
    public function posts()
    {
        $posts = Post::where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->get();

        $urls = $posts->map(function ($post) {
            return [
                'loc' => route('blog.show', $post->slug),
                'lastmod' => $post->updated_at->toISOString(),
                'changefreq' => 'weekly',
                'priority' => '0.7',
            ];
        })->toArray();

        $xml = view('sitemaps.urlset', compact('urls'))->render();

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }

    /**
     * Generate sitemap for products
     */
    public function products()
    {
        $products = Product::where('is_active', true)
            ->orderBy('updated_at', 'desc')
            ->get();

        $urls = $products->map(function ($product) {
            return [
                'loc' => route('products.show', $product->slug),
                'lastmod' => $product->updated_at->toISOString(),
                'changefreq' => 'weekly',
                'priority' => '0.9',
            ];
        })->toArray();

        $xml = view('sitemaps.urlset', compact('urls'))->render();

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }
}
