<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Product') }}: {{ $product->title }}
            </h2>
            <a href="{{ route('admin.products.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to Products
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white">
                    <form action="{{ route('admin.products.update', $product) }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- Title -->
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700">Title *</label>
                            <input type="text" 
                                   name="title" 
                                   id="title"
                                   value="{{ old('title', $product->title) }}"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('title') border-red-300 @enderror">
                            @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Description *</label>
                            <textarea name="description" 
                                      id="description"
                                      rows="4"
                                      required
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('description') border-red-300 @enderror">{{ old('description', $product->description) }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Price and Currency -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="price_yen" class="block text-sm font-medium text-gray-700">Price (¥) *</label>
                                <input type="number" 
                                       name="price_yen" 
                                       id="price_yen"
                                       value="{{ old('price_yen', $product->price_cents / 100) }}"
                                       min="0"
                                       step="1"
                                       required
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('price_yen') border-red-300 @enderror">
                                @error('price_yen')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="currency" class="block text-sm font-medium text-gray-700">Currency</label>
                                <select name="currency" 
                                        id="currency"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('currency') border-red-300 @enderror">
                                    <option value="jpy" {{ old('currency', strtolower($product->currency)) === 'jpy' ? 'selected' : '' }}>JPY (¥)</option>
                                    <option value="usd" {{ old('currency', strtolower($product->currency)) === 'usd' ? 'selected' : '' }}>USD ($)</option>
                                    <option value="eur" {{ old('currency', strtolower($product->currency)) === 'eur' ? 'selected' : '' }}>EUR (€)</option>
                                </select>
                                @error('currency')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Product Type and Status -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Product Type</label>
                                <div class="mt-2 space-y-2">
                                    <div class="flex items-center">
                                        <input type="radio" 
                                               name="is_digital" 
                                               id="digital"
                                               value="1"
                                               {{ old('is_digital', $product->is_digital ? '1' : '0') === '1' ? 'checked' : '' }}
                                               class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                        <label for="digital" class="ml-3 block text-sm font-medium text-gray-700">
                                            Digital Product
                                        </label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="radio" 
                                               name="is_digital" 
                                               id="physical"
                                               value="0"
                                               {{ old('is_digital', $product->is_digital ? '1' : '0') === '0' ? 'checked' : '' }}
                                               class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                        <label for="physical" class="ml-3 block text-sm font-medium text-gray-700">
                                            Physical Product
                                        </label>
                                    </div>
                                </div>
                                @error('is_digital')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Status</label>
                                <div class="mt-2 space-y-2">
                                    <div class="flex items-center">
                                        <input type="radio" 
                                               name="is_active" 
                                               id="active"
                                               value="1"
                                               {{ old('is_active', $product->is_active ? '1' : '0') === '1' ? 'checked' : '' }}
                                               class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                        <label for="active" class="ml-3 block text-sm font-medium text-gray-700">
                                            Active
                                        </label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="radio" 
                                               name="is_active" 
                                               id="inactive"
                                               value="0"
                                               {{ old('is_active', $product->is_active ? '1' : '0') === '0' ? 'checked' : '' }}
                                               class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                        <label for="inactive" class="ml-3 block text-sm font-medium text-gray-700">
                                            Inactive
                                        </label>
                                    </div>
                                </div>
                                @error('is_active')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- SEO Fields -->
                        <div class="border-t pt-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">SEO Settings (Optional)</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label for="seo_title" class="block text-sm font-medium text-gray-700">SEO Title</label>
                                    <input type="text" 
                                           name="seo_title" 
                                           id="seo_title"
                                           value="{{ old('seo_title', $product->seo_title) }}"
                                           maxlength="60"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('seo_title') border-red-300 @enderror">
                                    <p class="mt-1 text-sm text-gray-500">Recommended: 50-60 characters</p>
                                    @error('seo_title')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="seo_description" class="block text-sm font-medium text-gray-700">SEO Description</label>
                                    <textarea name="seo_description" 
                                              id="seo_description"
                                              rows="3"
                                              maxlength="160"
                                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('seo_description') border-red-300 @enderror">{{ old('seo_description', $product->seo_description) }}</textarea>
                                    <p class="mt-1 text-sm text-gray-500">Recommended: 150-160 characters</p>
                                    @error('seo_description')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Current File Attachments -->
                        @if($product->files->count() > 0)
                            <div class="border-t pt-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Current Attached Files</h3>
                                <div class="space-y-2">
                                    @foreach($product->files as $file)
                                        <div class="flex items-center justify-between bg-gray-50 p-3 rounded">
                                            <div>
                                                <span class="font-medium">{{ $file->original_name }}</span>
                                                <span class="text-gray-500 ml-2">({{ number_format($file->size_bytes / 1024, 1) }} KB)</span>
                                            </div>
                                            <form action="{{ route('admin.products.detach-file', [$product, $file]) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="text-red-600 hover:text-red-900 text-sm"
                                                        onclick="return confirm('Are you sure you want to detach this file?')">
                                                    Detach
                                                </button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Submit Buttons -->
                        <div class="flex justify-end space-x-4 pt-6 border-t">
                            <a href="{{ route('admin.products.index') }}" 
                               class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                Cancel
                            </a>
                            <button type="submit" 
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Update Product
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>