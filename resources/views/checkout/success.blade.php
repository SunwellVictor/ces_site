<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Payment Successful') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Success Message -->
            <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-8">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-lg font-medium text-green-800">
                            {{ __('Payment Successful!') }}
                        </h3>
                        <p class="mt-1 text-sm text-green-700">
                            {{ __('Thank you for your purchase. Your order has been processed and your downloads are now available.') }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Post-Purchase Hint -->
            <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-6 mb-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-indigo-800">
                                {{ __('Your Downloads Are Ready!') }}
                            </h3>
                            <p class="mt-1 text-sm text-indigo-700">
                                {{ __('Access your purchased files instantly from your Downloads page.') }}
                            </p>
                        </div>
                    </div>
                    <div class="flex-shrink-0">
                        <a href="{{ route('downloads.index') }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                            {{ __('Go to Downloads') }}
                            <svg class="ml-2 -mr-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Order Details -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">{{ __('Order Details') }}</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Order Number') }}</p>
                            <p class="font-semibold">#{{ $order->id }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Order Date') }}</p>
                            <p class="font-semibold">{{ $order->created_at->format('F j, Y \a\t g:i A') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Payment Method') }}</p>
                            <p class="font-semibold">{{ __('Stripe') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Total Amount') }}</p>
                            <p class="font-semibold text-lg">{{ currency($order->total_cents) }}</p>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div class="border-t pt-6">
                        <h4 class="font-semibold mb-4">{{ __('Items Purchased') }}</h4>
                        <div class="space-y-4">
                            @foreach($order->orderItems as $item)
                                <div class="flex justify-between items-center py-3 border-b border-gray-100 last:border-b-0">
                                    <div>
                                        <h5 class="font-medium">{{ $item->product->name }}</h5>
                                        <p class="text-sm text-gray-600">{{ __('Quantity: :qty', ['qty' => $item->qty]) }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-semibold">{{ currency($item->line_total_cents) }}</p>
                                        <p class="text-sm text-gray-600">{{ currency($item->unit_price_cents) }} {{ __('each') }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Call-to-Action Buttons -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">{{ __('What\'s Next?') }}</h3>
                    <p class="text-gray-600 mb-6">
                        {{ __('Your downloads are ready! Access them from your account dashboard or go directly to your downloads page.') }}
                    </p>
                    
                    <div class="flex flex-col sm:flex-row gap-4">
                        <!-- Primary CTA - Downloads -->
                        <a href="{{ route('downloads.index') }}" 
                           class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            {{ __('Access My Downloads') }}
                        </a>

                        <!-- Secondary CTA - Account Dashboard -->
                        <a href="{{ route('account.dashboard') }}" 
                           class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            {{ __('View Account Dashboard') }}
                        </a>

                        <!-- Tertiary CTA - Continue Shopping -->
                        <a href="{{ route('products.index') }}" 
                           class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                            {{ __('Continue Shopping') }}
                        </a>
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h4 class="text-sm font-medium text-blue-800">
                            {{ __('Important Information') }}
                        </h4>
                        <div class="mt-2 text-sm text-blue-700">
                            <ul class="list-disc list-inside space-y-1">
                                <li>{{ __('A confirmation email has been sent to your email address') }}</li>
                                <li>{{ __('Your downloads are available for 30 days from the purchase date') }}</li>
                                <li>{{ __('Each file can be downloaded up to 5 times') }}</li>
                                <li>{{ __('If you need assistance, please contact our support team') }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>