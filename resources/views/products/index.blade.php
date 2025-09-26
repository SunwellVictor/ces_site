<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Products') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Search and Filter Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="GET" action="{{ route('products.index') }}" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Search -->
                            <div>
                                <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                                <input type="text" 
                                       name="search" 
                                       id="search"
                                       value="{{ request('search') }}"
                                       placeholder="Search products..."
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <!-- Type Filter -->
                            <div>
                                <label for="type" class="block text-sm font-medium text-gray-700">Type</label>
                                <select name="type" id="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">All Types</option>
                                    <option value="digital" {{ request('type') === 'digital' ? 'selected' : '' }}>Digital</option>
                                    <option value="physical" {{ request('type') === 'physical' ? 'selected' : '' }}>Physical</option>
                                </select>
                            </div>

                            <!-- Price Range -->
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label for="min_price" class="block text-sm font-medium text-gray-700">Min Price (¥)</label>
                                    <input type="number" 
                                           name="min_price" 
                                           id="min_price"
                                           value="{{ request('min_price') }}"
                                           min="0"
                                           step="100"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label for="max_price" class="block text-sm font-medium text-gray-700">Max Price (¥)</label>
                                    <input type="number" 
                                           name="max_price" 
                                           id="max_price"
                                           value="{{ request('max_price') }}"
                                           min="0"
                                           step="100"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-between items-center">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Filter Products
                            </button>
                            <a href="{{ route('products.index') }}" class="text-gray-500 hover:text-gray-700">
                                Clear Filters
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white">
                    @if($products->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                            @foreach($products as $product)
                                <div class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200">
                                    <div class="p-4">
                                        <div class="flex justify-between items-start mb-2">
                                            <h3 class="text-lg font-semibold text-gray-900 line-clamp-2">
                                                <a href="{{ route('products.show', $product->slug) }}" class="hover:text-blue-600">
                                                    {{ $product->title }}
                                                </a>
                                            </h3>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $product->is_digital ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                                {{ $product->is_digital ? 'Digital' : 'Physical' }}
                                            </span>
                                        </div>

                                        <p class="text-gray-600 text-sm mb-4 line-clamp-3">
                                            {{ Str::limit($product->description, 120) }}
                                        </p>

                                        <div class="flex justify-between items-center">
                                            <div class="text-xl font-bold text-gray-900">
                                                ¥{{ number_format($product->price_cents / 100) }}
                                            </div>
                                            <div class="flex space-x-2">
                                                <a href="{{ route('products.show', $product->slug) }}" 
                                                   class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                                                    View Details
                                                </a>
                                                @auth
                                                    <form action="{{ route('cart.add', $product) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" 
                                                                class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm">
                                                            Add to Cart
                                                        </button>
                                                    </form>
                                                @endauth
                                            </div>
                                        </div>

                                        @if($product->files->count() > 0)
                                            <div class="mt-3 text-xs text-gray-500">
                                                {{ $product->files->count() }} file(s) included
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6">
                            {{ $products->withQueryString()->links() }}
                        </div>
                    @else
                        <div class="text-center py-12">
                            <div class="text-gray-500 text-lg">No products found.</div>
                            @if(request()->hasAny(['search', 'type', 'min_price', 'max_price']))
                                <a href="{{ route('products.index') }}" class="text-blue-500 hover:text-blue-700 mt-2 inline-block">
                                    Clear filters to see all products
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>