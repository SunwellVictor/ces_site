<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Product;
use App\Services\Meta;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Display the homepage with featured content.
     */
    public function index(Meta $meta)
    {
        // Set meta for homepage
        $meta->title(config('app.name') . ' - Digital Products & Educational Content')
             ->description('Discover premium digital products and educational content. Download high-quality resources for learning and development.')
             ->canonical(route('home'))
             ->og([
                 'type' => 'website',
                 'title' => config('app.name') . ' - Digital Products & Educational Content',
                 'description' => 'Discover premium digital products and educational content. Download high-quality resources for learning and development.',
                 'url' => route('home'),
             ])
             ->twitter([
                 'card' => 'summary_large_image',
                 'title' => config('app.name') . ' - Digital Products & Educational Content',
                 'description' => 'Discover premium digital products and educational content. Download high-quality resources for learning and development.',
             ]);

        // Get latest published posts for homepage
        $latestPosts = Post::where('status', 'published')
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->limit(3)
            ->get();

        // Get featured active products
        $featuredProducts = Product::where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->limit(4)
            ->get();

        return view('welcome', compact('latestPosts', 'featuredProducts'));
    }
}
