<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\Meta;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of active products.
     */
    public function index(Request $request, Meta $meta)
    {
        // Set meta for products index
        $title = 'Products - ' . config('app.name');
        $description = 'Browse our collection of premium digital products and educational resources. Find the perfect tools for your learning and development needs.';
        
        if ($request->has('search')) {
            $title = 'Search Results for "' . $request->search . '" - Products - ' . config('app.name');
            $description = 'Search results for "' . $request->search . '" in our product catalog.';
        }

        $meta->title($title)
             ->description($description)
             ->canonical(route('products.index'))
             ->og([
                 'type' => 'website',
                 'title' => $title,
                 'description' => $description,
                 'url' => route('products.index'),
             ])
             ->twitter([
                 'card' => 'summary',
                 'title' => $title,
                 'description' => $description,
             ]);

        $query = Product::where('is_active', true)
            ->orderBy('created_at', 'desc');

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by type (digital/physical)
        if ($request->has('type')) {
            if ($request->type === 'digital') {
                $query->where('is_digital', true);
            } elseif ($request->type === 'physical') {
                $query->where('is_digital', false);
            }
        }

        // Price range filter
        if ($request->has('min_price')) {
            $minPriceCents = (float) $request->min_price * 100;
            $query->where('price_cents', '>=', $minPriceCents);
        }

        if ($request->has('max_price')) {
            $maxPriceCents = (float) $request->max_price * 100;
            $query->where('price_cents', '<=', $maxPriceCents);
        }

        $products = $query->paginate(12);

        return view('products.index', compact('products'));
    }

    /**
     * Display the specified product.
     */
    public function show(string $slug, Meta $meta)
    {
        $product = Product::where('slug', $slug)
            ->where('is_active', true)
            ->with(['files'])
            ->firstOrFail();

        // Set meta for individual product using SEO fields or fallbacks
        $title = $product->seo_title ?: $product->title;
        $description = $product->seo_description ?: $product->description;
        $canonical = route('products.show', $product->slug);

        $meta->title($title)
             ->description($description)
             ->canonical($canonical)
             ->og([
                 'type' => 'product',
                 'title' => $title,
                 'description' => $description,
                 'url' => $canonical,
             ])
             ->twitter([
                 'card' => 'summary_large_image',
                 'title' => $title,
                 'description' => $description,
             ]);

        // Get related products (same type)
        $relatedProducts = Product::where('is_active', true)
            ->where('id', '!=', $product->id)
            ->where('is_digital', $product->is_digital)
            ->limit(4)
            ->get();

        return view('products.show', compact('product', 'relatedProducts'));
    }
}
