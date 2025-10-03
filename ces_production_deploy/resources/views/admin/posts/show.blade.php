@extends('layouts.admin')

@section('title', 'View Post: ' . $post->title)

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">View Post</h1>
        <div class="flex space-x-2">
            <a href="{{ route('blog.show', $post->slug) }}" target="_blank" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                View Live
            </a>
            <a href="{{ route('admin.posts.edit', $post) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                Edit Post
            </a>
            <a href="{{ route('admin.posts.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                Back to Posts
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <!-- Post Header -->
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ $post->title }}</h2>
                    <div class="flex items-center space-x-4 text-sm text-gray-600">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                            {{ $post->status === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ ucfirst($post->status) }}
                        </span>
                        <span>By {{ $post->author->name }}</span>
                        @if($post->published_at)
                            <span>Published {{ $post->published_at->format('M j, Y') }}</span>
                        @endif
                    </div>
                </div>

                <!-- Excerpt -->
                @if($post->excerpt)
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Excerpt</h3>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-gray-700">{{ $post->excerpt }}</p>
                        </div>
                    </div>
                @endif

                <!-- Content -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Content</h3>
                    <div class="prose max-w-none">
                        {!! nl2br(e($post->body)) !!}
                    </div>
                </div>

                <!-- SEO Information -->
                @if($post->seo_title || $post->seo_description)
                    <div class="border-t pt-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">SEO Information</h3>
                        <div class="space-y-4">
                            @if($post->seo_title)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">SEO Title</label>
                                    <p class="text-gray-900">{{ $post->seo_title }}</p>
                                </div>
                            @endif
                            @if($post->seo_description)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">SEO Description</label>
                                    <p class="text-gray-900">{{ $post->seo_description }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Post Information -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Post Information</h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                            {{ $post->status === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ ucfirst($post->status) }}
                        </span>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Author</label>
                        <p class="text-gray-900">{{ $post->author->name }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Slug</label>
                        <p class="text-gray-900 font-mono text-sm">{{ $post->slug }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Created</label>
                        <p class="text-gray-900">{{ $post->created_at->format('M j, Y g:i A') }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Last Updated</label>
                        <p class="text-gray-900">{{ $post->updated_at->format('M j, Y g:i A') }}</p>
                    </div>
                    
                    @if($post->published_at)
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Published</label>
                            <p class="text-gray-900">{{ $post->published_at->format('M j, Y g:i A') }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Categories -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Categories</h3>
                @if($post->categories->count() > 0)
                    <div class="space-y-2">
                        @foreach($post->categories as $category)
                            <div class="flex items-center justify-between">
                                <span class="inline-block bg-blue-100 text-blue-800 text-sm px-3 py-1 rounded-full">
                                    {{ $category->name }}
                                </span>
                                <a href="{{ route('admin.categories.show', $category) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                                    View
                                </a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-sm">No categories assigned</p>
                @endif
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                <div class="space-y-3">
                    @if($post->status === 'draft')
                        <form method="POST" action="{{ route('admin.posts.publish', $post) }}" class="w-full">
                            @csrf
                            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md transition-colors">
                                Publish Post
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.posts.unpublish', $post) }}" class="w-full">
                            @csrf
                            <button type="submit" class="w-full bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-md transition-colors">
                                Unpublish Post
                            </button>
                        </form>
                    @endif
                    
                    <form method="POST" action="{{ route('admin.posts.duplicate', $post) }}" class="w-full">
                        @csrf
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition-colors">
                            Duplicate Post
                        </button>
                    </form>
                    
                    <a href="{{ route('admin.posts.edit', $post) }}" class="block w-full text-center bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md transition-colors">
                        Edit Post
                    </a>
                    
                    <form method="POST" action="{{ route('admin.posts.destroy', $post) }}" class="w-full" onsubmit="return confirm('Are you sure you want to delete this post?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md transition-colors">
                            Delete Post
                        </button>
                    </form>
                </div>
            </div>

            <!-- URL Preview -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">URL Preview</h3>
                <div class="space-y-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Public URL</label>
                        <a href="{{ route('blog.show', $post->slug) }}" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm break-all">
                            {{ route('blog.show', $post->slug) }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection