@extends('layouts.app')

@section('title', 'Server Error - Clark English Learning')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 text-center">
        <div>
            <h1 class="text-9xl font-bold text-red-600">500</h1>
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                Server Error
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                We're experiencing some technical difficulties. Our team has been notified and is working to resolve the issue as quickly as possible.
            </p>
        </div>
        
        <div class="mt-8 space-y-4">
            <div>
                <a href="{{ route('home') }}" 
                   class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Try Again
                </a>
            </div>
            
            <div>
                <button onclick="window.history.back()" 
                        class="w-full py-2 px-4 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Go Back
                </button>
            </div>
        </div>
        
        <div class="mt-8 p-4 bg-yellow-50 border border-yellow-200 rounded-md">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">
                        Need immediate assistance?
                    </h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p>
                            Contact our support team at 
                            <a href="mailto:support@clarkenglish.com" class="font-medium underline text-yellow-700 hover:text-yellow-600">
                                support@clarkenglish.com
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-8">
            <p class="text-xs text-gray-500">
                Error ID: {{ uniqid() }} | Time: {{ now()->format('Y-m-d H:i:s T') }}
            </p>
        </div>
    </div>
</div>
@endsection