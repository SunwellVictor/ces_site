@extends('layouts.admin')

@section('title', 'Create New Post')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Create New Post</h1>
        <a href="{{ route('admin.posts.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
            Back to Posts
        </a>
    </div>

    <form method="POST" action="{{ route('admin.posts.store') }}" class="space-y-6">
        @csrf
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Title -->
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}" required
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('title') border-red-500 @enderror">
                        @error('title')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Slug -->
                    <div>
                        <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                        <input type="text" name="slug" id="slug" value="{{ old('slug') }}"
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('slug') border-red-500 @enderror">
                        <p class="mt-1 text-sm text-gray-500">Leave empty to auto-generate from title</p>
                        @error('slug')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Excerpt -->
                    <div>
                        <label for="excerpt" class="block text-sm font-medium text-gray-700 mb-1">Excerpt</label>
                        <textarea name="excerpt" id="excerpt" rows="3"
                                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('excerpt') border-red-500 @enderror">{{ old('excerpt') }}</textarea>
                        <p class="mt-1 text-sm text-gray-500">Leave empty to auto-generate from content (160 characters)</p>
                        @error('excerpt')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Body Content -->
                    <div>
                        <label for="body" class="block text-sm font-medium text-gray-700 mb-1">Content *</label>
                        <textarea name="body" id="body" rows="15" required
                                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('body') border-red-500 @enderror">{{ old('body') }}</textarea>
                        @error('body')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Publish Settings -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Publish Settings</h3>
                        
                        <!-- Status -->
                        <div class="mb-4">
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" id="status" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Published</option>
                            </select>
                        </div>

                        <!-- Published At -->
                        <div class="mb-4">
                            <label for="published_at" class="block text-sm font-medium text-gray-700 mb-1">Publish Date</label>
                            <input type="datetime-local" name="published_at" id="published_at" value="{{ old('published_at') }}"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <p class="mt-1 text-sm text-gray-500">Leave empty for immediate publish</p>
                        </div>

                        <!-- Categories -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Categories</label>
                            <div class="space-y-2 max-h-40 overflow-y-auto">
                                @foreach($categories as $category)
                                    <label class="flex items-center">
                                        <input type="checkbox" name="categories[]" value="{{ $category->id }}" 
                                               {{ in_array($category->id, old('categories', [])) ? 'checked' : '' }}
                                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                        <span class="ml-2 text-sm text-gray-700">{{ $category->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @if($categories->isEmpty())
                                <p class="text-sm text-gray-500">No categories available. <a href="{{ route('admin.categories.create') }}" class="text-blue-600 hover:text-blue-800">Create one</a></p>
                            @endif
                        </div>
                    </div>

                    <!-- SEO Settings -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">SEO Settings</h3>
                        
                        <!-- SEO Title -->
                        <div class="mb-4">
                            <label for="seo_title" class="block text-sm font-medium text-gray-700 mb-1">SEO Title</label>
                            <input type="text" name="seo_title" id="seo_title" value="{{ old('seo_title') }}"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <p class="mt-1 text-sm text-gray-500">Leave empty to use post title</p>
                        </div>

                        <!-- SEO Description -->
                        <div>
                            <label for="seo_description" class="block text-sm font-medium text-gray-700 mb-1">SEO Description</label>
                            <textarea name="seo_description" id="seo_description" rows="3"
                                      class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('seo_description') }}</textarea>
                            <p class="mt-1 text-sm text-gray-500">Leave empty to use excerpt</p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col space-y-3">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition-colors">
                            Create Post
                        </button>
                        <a href="{{ route('admin.posts.index') }}" class="w-full text-center bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md transition-colors">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const titleInput = document.getElementById('title');
    const slugInput = document.getElementById('slug');
    
    titleInput.addEventListener('input', function() {
        if (!slugInput.value || slugInput.dataset.autoGenerated) {
            const slug = this.value
                .toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim('-');
            slugInput.value = slug;
            slugInput.dataset.autoGenerated = 'true';
        }
    });
    
    slugInput.addEventListener('input', function() {
        if (this.value) {
            delete this.dataset.autoGenerated;
        }
    });
});
</script>
@endsection