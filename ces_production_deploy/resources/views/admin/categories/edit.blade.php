@extends('layouts.admin')

@section('title', 'Edit Category: ' . $category->name)

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Edit Category</h1>
        <div class="flex space-x-2">
            <a href="{{ route('blog.category', $category->slug) }}" target="_blank" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                View Live
            </a>
            <a href="{{ route('admin.categories.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                Back to Categories
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="space-y-6">
        @csrf
        @method('PUT')
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Category Name *</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $category->name) }}" required
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Slug -->
                    <div>
                        <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                        <input type="text" name="slug" id="slug" value="{{ old('slug', $category->slug) }}"
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('slug') border-red-500 @enderror">
                        <p class="mt-1 text-sm text-gray-500">Current URL: {{ url('/blog/category') }}/{{ $category->slug }}</p>
                        @error('slug')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="description" id="description" rows="4"
                                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('description') border-red-500 @enderror"
                                  placeholder="Optional description for this category...">{{ old('description', $category->description) }}</textarea>
                        <p class="mt-1 text-sm text-gray-500">This description will be shown on the category page and used for SEO.</p>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Category Settings -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Category Settings</h3>
                        
                        <!-- Status -->
                        <div class="mb-4">
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" id="status" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="active" {{ old('status', $category->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $category->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            <p class="mt-1 text-sm text-gray-500">Inactive categories won't be shown on the public site.</p>
                        </div>
                    </div>

                    <!-- Category Information -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Category Information</h3>
                        <div class="space-y-2 text-sm text-gray-600">
                            <div><strong>Posts:</strong> {{ $category->posts()->count() }} {{ Str::plural('post', $category->posts()->count()) }}</div>
                            <div><strong>Created:</strong> {{ $category->created_at->format('M j, Y g:i A') }}</div>
                            <div><strong>Updated:</strong> {{ $category->updated_at->format('M j, Y g:i A') }}</div>
                        </div>
                    </div>

                    <!-- SEO Preview -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">SEO Preview</h3>
                        <div class="space-y-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">URL Preview</label>
                                <p class="text-sm text-gray-600 break-all">
                                    {{ url('/blog/category') }}/<span id="url-preview">{{ $category->slug }}</span>
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Page Title</label>
                                <p class="text-sm text-gray-600" id="title-preview">{{ $category->name }} - Blog</p>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col space-y-3">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition-colors">
                            Update Category
                        </button>
                        
                        <form method="POST" action="{{ route('admin.categories.toggle-status', $category) }}" class="w-full">
                            @csrf
                            <button type="submit" class="w-full bg-{{ $category->status === 'active' ? 'yellow' : 'green' }}-600 hover:bg-{{ $category->status === 'active' ? 'yellow' : 'green' }}-700 text-white px-4 py-2 rounded-md transition-colors">
                                {{ $category->status === 'active' ? 'Deactivate' : 'Activate' }} Category
                            </button>
                        </form>
                        
                        <a href="{{ route('admin.categories.index') }}" class="w-full text-center bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md transition-colors">
                            Cancel
                        </a>
                        
                        @if($category->posts()->count() === 0)
                            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="w-full" onsubmit="return confirm('Are you sure you want to delete this category?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md transition-colors">
                                    Delete Category
                                </button>
                            </form>
                        @else
                            <div class="w-full bg-red-100 text-red-700 px-4 py-2 rounded-md text-center text-sm">
                                Cannot delete: Category has {{ $category->posts()->count() }} {{ Str::plural('post', $category->posts()->count()) }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Related Posts -->
    @if($category->posts()->count() > 0)
        <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Posts in this Category</h3>
                <a href="{{ route('admin.categories.posts', $category) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                    View All Posts
                </a>
            </div>
            
            <div class="space-y-3">
                @foreach($category->posts()->latest()->limit(5)->get() as $post)
                    <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-b-0">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900">{{ $post->title }}</h4>
                            <p class="text-xs text-gray-500">
                                {{ $post->status === 'published' ? 'Published' : 'Draft' }} • 
                                {{ $post->published_at ? $post->published_at->format('M j, Y') : $post->created_at->format('M j, Y') }}
                            </p>
                        </div>
                        <div class="flex space-x-2">
                            <a href="{{ route('admin.posts.edit', $post) }}" class="text-blue-600 hover:text-blue-800 text-sm">Edit</a>
                            @if($post->status === 'published')
                                <a href="{{ route('blog.show', $post->slug) }}" target="_blank" class="text-green-600 hover:text-green-800 text-sm">View</a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');
    const urlPreview = document.getElementById('url-preview');
    const titlePreview = document.getElementById('title-preview');
    const originalSlug = slugInput.value;
    
    function updatePreviews() {
        const name = nameInput.value || 'Category Name';
        const slug = slugInput.value || 'category-slug';
        
        urlPreview.textContent = slug;
        titlePreview.textContent = name + ' - Blog';
    }
    
    function generateSlug(text) {
        return text
            .toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .trim('-');
    }
    
    nameInput.addEventListener('input', function() {
        if (!slugInput.value || slugInput.value === originalSlug || slugInput.dataset.autoGenerated) {
            const slug = generateSlug(this.value);
            slugInput.value = slug;
            slugInput.dataset.autoGenerated = 'true';
        }
        updatePreviews();
    });
    
    slugInput.addEventListener('input', function() {
        if (this.value !== originalSlug) {
            delete this.dataset.autoGenerated;
        }
        updatePreviews();
    });
    
    // Initial preview update
    updatePreviews();
});
</script>
@endsection