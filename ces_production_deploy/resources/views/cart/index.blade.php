<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Shopping Cart') }}
        </h2>
    </x-slot>
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">Shopping Cart</h1>
    
    @if(empty($cartItems))
        <div class="text-center py-12">
            <p class="text-gray-600 text-lg mb-4">Your cart is empty</p>
            <a href="{{ route('products.index') }}" class="bg-blue-500 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 focus:outline-none text-white font-bold py-2 px-4 rounded transition-colors">
                Continue Shopping
            </a>
        </div>
    @else
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($cartItems as $productId => $item)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $item['title'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ currency($item['unit_price_cents']) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $item['qty'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ currency($item['unit_price_cents'] * $item['qty']) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <form action="{{ route('cart.remove', $productId) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            
            <div class="px-6 py-4 bg-gray-50">
                <div class="flex justify-between items-center">
                    <div class="text-lg font-semibold">
                        Total: {{ currency($cartTotal) }}
                    </div>
                    <div class="space-x-4">
                        <a href="{{ route('products.index') }}" class="bg-gray-500 hover:bg-gray-700 focus:ring-4 focus:ring-gray-300 focus:outline-none text-white font-bold py-2 px-4 rounded transition-colors">
                            Continue Shopping
                        </a>
                        @auth
                            <a href="{{ route('checkout.create') }}" class="bg-blue-500 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 focus:outline-none text-white font-bold py-2 px-4 rounded transition-colors">
                                Checkout
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="bg-blue-500 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 focus:outline-none text-white font-bold py-2 px-4 rounded transition-colors">
                                Login to Checkout
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
</x-app-layout>