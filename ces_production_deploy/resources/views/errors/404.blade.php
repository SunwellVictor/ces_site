@extends('layouts.app')

@section('title', 'Page Not Found - Clark English Learning')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 text-center">
        <div>
            <h1 class="text-9xl font-bold text-blue-600">404</h1>
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                Page Not Found
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                Sorry, we couldn't find the page you're looking for. The page may have been moved, deleted, or you may have entered an incorrect URL.
            </p>
        </div>
        
        <div class="mt-8 space-y-4">
            <div>
                <a href="{{ route('home') }}" 
                   class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Go to Homepage
                </a>
            </div>
            
            <div class="flex space-x-4">
                <a href="{{ route('blog.index') }}" 
                   class="flex-1 text-center py-2 px-4 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Blog
                </a>
                <a href="{{ route('products.index') }}" 
                   class="flex-1 text-center py-2 px-4 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Products
                </a>
            </div>
        </div>
        
        <div class="mt-8">
            <p class="text-xs text-gray-500">
                If you believe this is an error, please 
                <a href="mailto:support@clarkenglish.com" class="text-blue-600 hover:text-blue-500">contact support</a>.
            </p>
        </div>
    </div>
</div>
@endsection