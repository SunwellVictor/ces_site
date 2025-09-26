<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Blog') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Search and Filter Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('blog.index') }}" class="flex flex-col md:flex-row gap-4">
                        <!-- Search Input -->
                        <div class="flex-1">
                            <input type="text" 
                                   name="search" 
                                   value="{{ request('search') }}"
                                   placeholder="Search posts..." 
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        
                        <!-- Category Filter -->
                        <div class="md:w-48">
                            <select name="category" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->slug }}" {{ request('category') == $category->slug ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Search Button -->
                        <div>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Search
                            </button>
                        </div>
                        
                        <!-- Clear Filters -->
                        @if(request('search') || request('category'))
                            <div>
                                <a href="{{ route('blog.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                    Clear
                                </a>
                            </div>
                        @endif
                    </form>
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
                                
                                <!-- Categories -->
                                @if($post->categories->count() > 0)
                                    <div class="mb-3">
                                        @foreach($post->categories as $category)
                                            <a href="{{ route('blog.category', $category->slug) }}" 
                                               class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full mr-2 hover:bg-blue-200 transition-colors">
                                                {{ $category->name }}
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
                            @if(request('search') || request('category'))
                                Try adjusting your search criteria or browse all posts.
                            @else
                                Check back later for new content!
                            @endif
                        </p>
                        @if(request('search') || request('category'))
                            <a href="{{ route('blog.index') }}" class="mt-4 inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                View All Posts
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>