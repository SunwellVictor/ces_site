<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /**
     * Display a listing of products with search and filtering.
     */
    public function index(Request $request)
    {
        $query = Product::with('files');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Type filter
        if ($request->filled('is_digital')) {
            $query->where('is_digital', $request->boolean('is_digital'));
        }

        $products = $query->latest()->paginate(20);

        return view('admin.products.index', compact('products'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        $files = File::all();
        return view('admin.products.create', compact('files'));
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price_yen' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'max:3'],
            'is_active' => ['boolean'],
            'is_digital' => ['boolean'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],
            'files' => ['array'],
            'files.*' => ['exists:files,id'],
        ]);

        // Convert yen to cents
        $validated['price_cents'] = (int) ($validated['price_yen'] * 100);
        unset($validated['price_yen']);

        // Generate slug from title
        $validated['slug'] = Str::slug($validated['title']);
        
        // Set defaults
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['is_digital'] = $request->boolean('is_digital', true);
        $validated['currency'] = $validated['currency'] ?? 'JPY';

        $product = Product::create($validated);

        // Attach files if provided
        if (!empty($validated['files'])) {
            $product->files()->sync($validated['files']);
        }

        return redirect()->route('admin.products.index')
                        ->with('success', 'Product created successfully.');
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        $product->load(['files', 'orders.user']);
        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        $files = File::all();
        $product->load('files');
        return view('admin.products.edit', compact('product', 'files'));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price_yen' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'max:3'],
            'is_active' => ['boolean'],
            'is_digital' => ['boolean'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],
            'files' => ['array'],
            'files.*' => ['exists:files,id'],
        ]);

        // Convert yen to cents
        $validated['price_cents'] = (int) ($validated['price_yen'] * 100);
        unset($validated['price_yen']);

        // Generate slug from title
        $validated['slug'] = Str::slug($validated['title']);
        
        // Set boolean values
        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_digital'] = $request->boolean('is_digital');

        $product->update($validated);

        // Update file attachments
        if (isset($validated['files'])) {
            $product->files()->sync($validated['files']);
        }

        return redirect()->route('admin.products.index')
                        ->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product)
    {
        // Check if product has order items
        if ($product->orderItems()->exists()) {
            return redirect()->route('admin.products.index')
                            ->with('error', 'Cannot delete product with existing orders.');
        }

        // Detach files before deletion
        $product->files()->detach();
        
        $product->delete();

        return redirect()->route('admin.products.index')
                        ->with('success', 'Product deleted successfully.');
    }

    /**
     * Attach files to a product.
     */
    public function attachFiles(Request $request, Product $product)
    {
        $validated = $request->validate([
            'files' => ['required', 'array'],
            'files.*' => ['exists:files,id'],
        ]);

        $product->files()->syncWithoutDetaching($validated['files']);

        return redirect()->route('admin.products.show', $product)
                        ->with('success', 'Files attached successfully.');
    }

    /**
     * Detach a file from a product.
     */
    public function detachFile(Product $product, File $file)
    {
        $product->files()->detach($file->id);

        return redirect()->route('admin.products.show', $product)
                        ->with('success', 'File detached successfully.');
    }
}
