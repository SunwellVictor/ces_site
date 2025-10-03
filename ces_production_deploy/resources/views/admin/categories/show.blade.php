@extends('layouts.admin')

@section('title', 'Category: ' . $category->name)

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">{{ $category->name }}</h1>
        <div class="flex space-x-2">
            <a href="{{ route('blog.category', $category->slug) }}" target="_blank" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                View Live
            </a>
            <a href="{{ route('admin.categories.edit', $category) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                Edit Category
            </a>
            <a href="{{ route('admin.categories.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                Back to Categories
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Category Details -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Category Details</h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Name</label>
                        <p class="text-lg text-gray-900">{{ $category->name }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Slug</label>
                        <p class="text-gray-900 font-mono">{{ $category->slug }}</p>
                        <p class="text-sm text-gray-500">{{ url('/blog/category/' . $category->slug) }}</p>
                    </div>
                    
                    @if($category->description)
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Description</label>
                            <div class="prose prose-sm max-w-none text-gray-900">
                                {!! nl2br(e($category->description)) !!}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Posts in Category -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold text-gray-900">
                        Posts in this Category ({{ $category->posts()->count() }})
                    </h2>
                    @if($category->posts()->count() > 0)
                        <a href="{{ route('admin.categories.posts', $category) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                            View All Posts
                        </a>
                    @endif
                </div>
                
                @if($category->posts()->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Author</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Published</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($category->posts()->latest()->paginate(10) as $post)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $post->title }}</div>
                                            @if($post->excerpt)
                                                <div class="text-sm text-gray-500">{{ Str::limit($post->excerpt, 60) }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $post->author->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                                {{ $post->status === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                {{ ucfirst($post->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $post->published_at ? $post->published_at->format('M j, Y') : '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <a href="{{ route('admin.posts.show', $post) }}" class="text-blue-600 hover:text-blue-900">View</a>
                                                <a href="{{ route('admin.posts.edit', $post) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                                @if($post->status === 'published')
                                                    <a href="{{ route('blog.show', $post->slug) }}" target="_blank" class="text-green-600 hover:text-green-900">Live</a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    @if($category->posts()->count() > 10)
                        <div class="mt-4">
                            {{ $category->posts()->latest()->paginate(10)->links() }}
                        </div>
                    @endif
                @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No posts in this category</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by creating a new post and assigning it to this category.</p>
                        <div class="mt-6">
                            <a href="{{ route('admin.posts.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                Create New Post
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Category Information -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Category Information</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Status:</span>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                            {{ $category->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ ucfirst($category->status) }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Total Posts:</span>
                        <span class="text-sm font-medium text-gray-900">{{ $category->posts()->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Published Posts:</span>
                        <span class="text-sm font-medium text-gray-900">{{ $category->posts()->where('status', 'published')->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Draft Posts:</span>
                        <span class="text-sm font-medium text-gray-900">{{ $category->posts()->where('status', 'draft')->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Created:</span>
                        <span class="text-sm text-gray-900">{{ $category->created_at->format('M j, Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Updated:</span>
                        <span class="text-sm text-gray-900">{{ $category->updated_at->format('M j, Y') }}</span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
                <div class="space-y-3">
                    <a href="{{ route('admin.categories.edit', $category) }}" 
                       class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition-colors text-center block">
                        Edit Category
                    </a>
                    
                    <form method="POST" action="{{ route('admin.categories.toggle-status', $category) }}" class="w-full">
                        @csrf
                        <button type="submit" class="w-full bg-{{ $category->status === 'active' ? 'yellow' : 'green' }}-600 hover:bg-{{ $category->status === 'active' ? 'yellow' : 'green' }}-700 text-white px-4 py-2 rounded-md transition-colors">
                            {{ $category->status === 'active' ? 'Deactivate' : 'Activate' }} Category
                        </button>
                    </form>
                    
                    <a href="{{ route('admin.posts.create') }}?category={{ $category->id }}" 
                       class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md transition-colors text-center block">
                        Create Post in Category
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
                            Cannot delete: Category has posts
                        </div>
                    @endif
                </div>
            </div>

            <!-- SEO Information -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">SEO Information</h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Public URL</label>
                        <a href="{{ route('blog.category', $category->slug) }}" target="_blank" 
                           class="text-sm text-blue-600 hover:text-blue-800 break-all">
                            {{ route('blog.category', $category->slug) }}
                        </a>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Page Title</label>
                        <p class="text-sm text-gray-900">{{ $category->name }} - Blog</p>
                    </div>
                    @if($category->description)
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Meta Description</label>
                            <p class="text-sm text-gray-900">{{ Str::limit($category->description, 160) }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection