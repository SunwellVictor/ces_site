<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Blog') }} - {{ $category->name }}
            </h2>
            <a href="{{ route('blog.index') }}" class="text-blue-600 hover:text-blue-800 transition-colors">
                ← All Posts
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Category Header -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ $category->name }}</h1>
                    <p class="text-gray-600">
                        Showing {{ $posts->total() }} {{ Str::plural('post', $posts->total()) }} in this category
                    </p>
                </div>
            </div>

            <!-- Search Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('blog.category', $category->slug) }}" class="flex flex-col md:flex-row gap-4">
                        <!-- Search Input -->
                        <div class="flex-1">
                            <input type="text" 
                                   name="search" 
                                   value="{{ request('search') }}"
                                   placeholder="Search posts in {{ $category->name }}..." 
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        
                        <!-- Search Button -->
                        <div>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Search
                            </button>
                        </div>
                        
                        <!-- Clear Search -->
                        @if(request('search'))
                            <div>
                                <a href="{{ route('blog.category', $category->slug) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                    Clear
                                </a>
                            </div>
                        @endif
                    </form>
                </div>
            </div>

            <!-- Category Navigation -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">Browse Categories</h3>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('blog.index') }}" 
                           class="inline-block bg-gray-100 text-gray-800 text-sm px-3 py-1 rounded-full hover:bg-gray-200 transition-colors">
                            All Posts
                        </a>
                        @foreach($categories as $cat)
                            <a href="{{ route('blog.category', $cat->slug) }}" 
                               class="inline-block text-sm px-3 py-1 rounded-full transition-colors {{ $cat->id === $category->id ? 'bg-blue-500 text-white' : 'bg-blue-100 text-blue-800 hover:bg-blue-200' }}">
                                {{ $cat->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Posts Grid -->
            @if($posts->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($posts as $post)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-lg transition-shadow duration-300">
                            <div class="p-6">
                                <!-- Post Title -->
                                <h3 class="text-xl font-semibold text-gray-900 mb-2">
                                    <a href="{{ route('blog.show', $post->slug) }}" class="hover:text-blue-600 transition-colors">
                                        {{ $post->title }}
                                    </a>
                                </h3>
                                
                                <!-- Post Meta -->
                                <div class="text-sm text-gray-600 mb-3">
                                    <span>By {{ $post->author->name }}</span>
                                    <span class="mx-2">•</span>
                                    <span>{{ $post->published_at->format('M j, Y') }}</span>
                                </div>
                                
                                <!-- Other Categories -->
                                @php
                                    $otherCategories = $post->categories->where('id', '!=', $category->id);
                                @endphp
                                @if($otherCategories->count() > 0)
                                    <div class="mb-3">
                                        <span class="text-xs text-gray-500">Also in:</span>
                                        @foreach($otherCategories as $otherCategory)
                                            <a href="{{ route('blog.category', $otherCategory->slug) }}" 
                                               class="inline-block bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded-full ml-1 hover:bg-gray-200 transition-colors">
                                                {{ $otherCategory->name }}
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                                
                                <!-- Excerpt -->
                                <p class="text-gray-700 mb-4">{{ $post->excerpt }}</p>
                                
                                <!-- Read More Link -->
                                <a href="{{ route('blog.show', $post->slug) }}" 
                                   class="text-blue-600 hover:text-blue-800 font-medium transition-colors">
                                    Read more →
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <!-- Pagination -->
                <div class="mt-8">
                    {{ $posts->appends(request()->query())->links() }}
                </div>
            @else
                <!-- No Posts Found -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No posts found</h3>
                        <p class="text-gray-600">
                            @if(request('search'))
                                No posts in "{{ $category->name }}" match your search for "{{ request('search') }}".
                            @else
                                There are no published posts in the "{{ $category->name }}" category yet.
                            @endif
                        </p>
                        <div class="mt-4 space-x-4">
                            @if(request('search'))
                                <a href="{{ route('blog.category', $category->slug) }}" class="inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    View All in {{ $category->name }}
                                </a>
                            @endif
                            <a href="{{ route('blog.index') }}" class="inline-block bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Browse All Posts
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>