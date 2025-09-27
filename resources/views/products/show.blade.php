<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $product->title }}
            </h2>
            <a href="{{ route('products.index') }}" class="text-blue-500 hover:text-blue-700">
                ← Back to Products
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Product Info -->
                        <div>
                            <div class="mb-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $product->is_digital ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                    {{ $product->is_digital ? 'Digital Product' : 'Physical Product' }}
                                </span>
                            </div>

                            <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ $product->title }}</h1>
                            
                            <div class="text-4xl font-bold text-gray-900 mb-6">
                                {{ currency($product->price_cents) }}
                                <span class="text-lg font-normal text-gray-500">{{ strtoupper($product->currency) }}</span>
                            </div>

                            <div class="prose max-w-none mb-6">
                                <p class="text-gray-700 leading-relaxed">{{ $product->description }}</p>
                            </div>

                            <!-- Files Information -->
                            @if($product->files->count() > 0)
                                <div class="mb-6">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-3">Included Files</h3>
                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <ul class="space-y-2">
                                            @foreach($product->files as $file)
                                                <li class="flex items-center justify-between">
                                                    <div class="flex items-center">
                                                        <svg class="w-5 h-5 text-gray-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                                                        </svg>
                                                        <span class="text-sm text-gray-700">{{ $file->original_name }}</span>
                                                    </div>
                                                    <span class="text-xs text-gray-500">
                                                        {{ number_format($file->size_bytes / 1024, 1) }} KB
                                                    </span>
                                                </li>
                                            @endforeach
                                        </ul>
                                        <div class="mt-3 text-sm text-gray-600">
                                            Total: {{ $product->files->count() }} file(s)
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Purchase Actions -->
                            <div class="space-y-4">
                                @auth
                                    <form action="{{ route('cart.add', $product) }}" method="POST">
                                        @csrf
                                        <div class="flex space-x-4">
                                            <button type="submit" 
                                                    class="flex-1 bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 focus:outline-none text-white font-bold py-3 px-6 rounded-lg transition duration-200">
                                                Add to Cart
                                            </button>
                                            <a href="{{ route('cart.index') }}" 
                                               class="bg-gray-200 hover:bg-gray-300 focus:ring-4 focus:ring-gray-300 focus:outline-none text-gray-800 font-bold py-3 px-6 rounded-lg transition duration-200">
                                                View Cart
                                            </a>
                                        </div>
                                    </form>
                                @else
                                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                        <p class="text-yellow-800 mb-3">Please log in to purchase this product.</p>
                                        <div class="flex space-x-4">
                                            <a href="{{ route('login') }}" 
                                               class="bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 focus:outline-none text-white font-bold py-2 px-4 rounded transition-colors">
                                                Log In
                                            </a>
                                            <a href="{{ route('register') }}" 
                                               class="bg-gray-200 hover:bg-gray-300 focus:ring-4 focus:ring-gray-300 focus:outline-none text-gray-800 font-bold py-2 px-4 rounded transition-colors">
                                                Register
                                            </a>
                                        </div>
                                    </div>
                                @endauth
                            </div>
                        </div>

                        <!-- Product Details -->
                        <div>
                            <div class="bg-gray-50 rounded-lg p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Product Details</h3>
                                <dl class="space-y-3">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Type</dt>
                                        <dd class="text-sm text-gray-900">{{ $product->is_digital ? 'Digital Download' : 'Physical Product' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Price</dt>
                                        <dd class="text-sm text-gray-900">{{ currency($product->price_cents) }} {{ strtoupper($product->currency) }}</dd>
                                    </div>
                                    @if($product->files->count() > 0)
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Files Included</dt>
                                            <dd class="text-sm text-gray-900">{{ $product->files->count() }} file(s)</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Total Size</dt>
                                            <dd class="text-sm text-gray-900">
                                                {{ number_format($product->files->sum('size_bytes') / 1024 / 1024, 2) }} MB
                                            </dd>
                                        </div>
                                    @endif
                                    @if($product->is_digital)
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Delivery</dt>
                                            <dd class="text-sm text-gray-900">Instant download after purchase</dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>

                            <!-- SEO Information (if available) -->
                            @if($product->seo_title || $product->seo_description)
                                <div class="mt-6 bg-blue-50 rounded-lg p-6">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Additional Information</h3>
                                    @if($product->seo_title)
                                        <div class="mb-3">
                                            <dt class="text-sm font-medium text-gray-500">SEO Title</dt>
                                            <dd class="text-sm text-gray-900">{{ $product->seo_title }}</dd>
                                        </div>
                                    @endif
                                    @if($product->seo_description)
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">SEO Description</dt>
                                            <dd class="text-sm text-gray-900">{{ $product->seo_description }}</dd>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Related Products -->
            @if($relatedProducts->count() > 0)
                <div class="mt-8 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white">
                        <h3 class="text-xl font-semibold text-gray-900 mb-6">Related Products</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            @foreach($relatedProducts as $relatedProduct)
                                <div class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200">
                                    <div class="p-4">
                                        <h4 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2">
                                            <a href="{{ route('products.show', $relatedProduct->slug) }}" class="hover:text-blue-600">
                                                {{ $relatedProduct->title }}
                                            </a>
                                        </h4>
                                        <p class="text-gray-600 text-sm mb-4 line-clamp-2">
                                            {{ Str::limit($relatedProduct->description, 80) }}
                                        </p>
                                        <div class="flex justify-between items-center">
                                            <div class="text-lg font-bold text-gray-900">
                                                {{ currency($relatedProduct->price_cents) }}
                                            </div>
                                            <a href="{{ route('products.show', $relatedProduct->slug) }}" 
                                               class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded text-sm">
                                                View
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('head')
        @if($product->seo_title)
            <title>{{ $product->seo_title }}</title>
        @endif
        @if($product->seo_description)
            <meta name="description" content="{{ $product->seo_description }}">
        @endif
        <meta property="og:title" content="{{ $product->seo_title ?? $product->title }}">
        <meta property="og:description" content="{{ $product->seo_description ?? Str::limit($product->description, 160) }}">
        <meta property="og:type" content="product">
        <meta property="product:price:amount" content="{{ $product->price_cents / 100 }}">
        <meta property="product:price:currency" content="{{ strtoupper($product->currency) }}">
        
        {{-- JSON-LD Schema --}}
        @include('partials.schema.product', ['product' => $product])
    @endpush
</x-app-layout>