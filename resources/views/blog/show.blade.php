<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $post->title }}
            </h2>
            <a href="{{ route('blog.index') }}" class="text-blue-600 hover:text-blue-800 transition-colors">
                ← Back to Blog
            </a>
        </div>
    </x-slot>

    <!-- SEO Meta Tags -->
    @push('meta')
        <title>{{ $post->seo_title ?? $post->title }} - {{ config('app.name') }}</title>
        <meta name="description" content="{{ $post->seo_description ?? $post->excerpt }}">
        <meta property="og:title" content="{{ $post->seo_title ?? $post->title }}">
        <meta property="og:description" content="{{ $post->seo_description ?? $post->excerpt }}">
        <meta property="og:type" content="article">
        <meta property="og:url" content="{{ route('blog.show', $post->slug) }}">
        <meta property="article:published_time" content="{{ $post->published_at->toISOString() }}">
        <meta property="article:author" content="{{ $post->author->name }}">
        @foreach($post->categories as $category)
            <meta property="article:section" content="{{ $category->name }}">
        @endforeach
    @endpush

    <!-- JSON-LD Schema -->
    @push('head')
        @include('partials.schema.article', ['post' => $post])
    @endpush

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Main Post Content -->
            <article class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8">
                    <!-- Post Header -->
                    <header class="mb-8">
                        <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ $post->title }}</h1>
                        
                        <!-- Post Meta -->
                        <div class="flex flex-wrap items-center text-sm text-gray-600 mb-4">
                            <span>By <strong>{{ $post->author->name }}</strong></span>
                            <span class="mx-2">•</span>
                            <time datetime="{{ $post->published_at->toISOString() }}">
                                {{ $post->published_at->format('F j, Y') }}
                            </time>
                            <span class="mx-2">•</span>
                            <span>{{ ceil(str_word_count(strip_tags($post->body)) / 200) }} min read</span>
                        </div>
                        
                        <!-- Categories -->
                        @if($post->categories->count() > 0)
                            <div class="mb-6">
                                <span class="text-sm text-gray-600 mr-2">Categories:</span>
                                @foreach($post->categories as $category)
                                    <a href="{{ route('blog.category', $category->slug) }}" 
                                       class="inline-block bg-blue-100 text-blue-800 text-sm px-3 py-1 rounded-full mr-2 hover:bg-blue-200 transition-colors">
                                        {{ $category->name }}
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </header>
                    
                    <!-- Post Content -->
                    <div class="prose prose-lg max-w-none">
                        {!! nl2br(e($post->body)) !!}
                    </div>
                </div>
            </article>
            
            <!-- Related Posts -->
            @if($relatedPosts->count() > 0)
                <div class="mt-12">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Related Posts</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @foreach($relatedPosts as $relatedPost)
                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-lg transition-shadow duration-300">
                                <div class="p-6">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                        <a href="{{ route('blog.show', $relatedPost->slug) }}" class="hover:text-blue-600 transition-colors">
                                            {{ $relatedPost->title }}
                                        </a>
                                    </h3>
                                    
                                    <div class="text-sm text-gray-600 mb-3">
                                        {{ $relatedPost->published_at->format('M j, Y') }}
                                    </div>
                                    
                                    <p class="text-gray-700 text-sm mb-3">{{ Str::limit($relatedPost->excerpt, 100) }}</p>
                                    
                                    <a href="{{ route('blog.show', $relatedPost->slug) }}" 
                                       class="text-blue-600 hover:text-blue-800 font-medium text-sm transition-colors">
                                        Read more →
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            
            <!-- Navigation -->
            <div class="mt-8 flex justify-between">
                <a href="{{ route('blog.index') }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded transition-colors">
                    ← All Posts
                </a>
                
                @if($post->categories->count() > 0)
                    <a href="{{ route('blog.category', $post->categories->first()->slug) }}" 
                       class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition-colors">
                        More in {{ $post->categories->first()->name }} →
                    </a>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>