<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Order Details') }} - #{{ $order->id }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('admin.orders.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Orders
                </a>
                @if($order->status === 'pending')
                    <form method="POST" action="{{ route('admin.orders.mark-completed', $order) }}" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" 
                                class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
                                onclick="return confirm('Mark this order as paid?')">
                            Mark Paid
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.orders.mark-failed', $order) }}" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" 
                                class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded"
                                onclick="return confirm('Mark this order as failed?')">
                            Mark Failed
                        </button>
                    </form>
                @endif
                @if($order->status === 'completed' && !$order->refunded_at)
                    <a href="{{ route('admin.orders.refund', $order) }}" class="bg-orange-500 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded">
                        Process Refund
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Order Summary -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Order Information -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Order Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Order ID</label>
                                    <p class="mt-1 text-sm text-gray-900">#{{ $order->id }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Status</label>
                                    <div class="mt-1">
                                        @switch($order->status)
                                            @case('paid')
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                    Paid
                                                </span>
                                                @break
                                            @case('pending')
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    Pending
                                                </span>
                                                @break
                                            @case('failed')
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                    Failed
                                                </span>
                                                @break
                                            @case('refunded')
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                                    Refunded
                                                </span>
                                                @break
                                            @default
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                                    {{ ucfirst($order->status) }}
                                                </span>
                                        @endswitch
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Total Amount</label>
                                    <p class="mt-1 text-sm text-gray-900">¥{{ number_format($order->total_cents / 100) }} {{ strtoupper($order->currency) }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Order Date</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $order->created_at->format('M j, Y g:i A') }}</p>
                                </div>
                                @if($order->completed_at)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Completed Date</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ $order->completed_at->format('M j, Y g:i A') }}</p>
                                    </div>
                                @endif
                                @if($order->refunded_at)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Refunded Date</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ $order->refunded_at->format('M j, Y g:i A') }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Refund Amount</label>
                                        <p class="mt-1 text-sm text-gray-900">¥{{ number_format(($order->refund_amount_cents ?? $order->total_cents) / 100) }}</p>
                                    </div>
                                    @if($order->refund_reason)
                                        <div class="md:col-span-2">
                                            <label class="block text-sm font-medium text-gray-700">Refund Reason</label>
                                            <p class="mt-1 text-sm text-gray-900">{{ $order->refund_reason }}</p>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Stripe Information -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Stripe Payment Information</h3>
                            <div class="grid grid-cols-1 gap-4">
                                @if($order->stripe_session_id)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Stripe Session ID</label>
                                        <div class="mt-1 flex items-center space-x-2">
                                            <code class="text-sm bg-gray-100 px-2 py-1 rounded font-mono">{{ $order->stripe_session_id }}</code>
                                            <button onclick="copyToClipboard('{{ $order->stripe_session_id }}')" 
                                                    class="text-indigo-600 hover:text-indigo-900 text-sm">
                                                Copy
                                            </button>
                                        </div>
                                    </div>
                                @endif
                                @if($order->stripe_payment_intent)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Stripe Payment Intent ID</label>
                                        <div class="mt-1 flex items-center space-x-2">
                                            <code class="text-sm bg-gray-100 px-2 py-1 rounded font-mono">{{ $order->stripe_payment_intent }}</code>
                                            <button onclick="copyToClipboard('{{ $order->stripe_payment_intent }}')" 
                                                    class="text-indigo-600 hover:text-indigo-900 text-sm">
                                                Copy
                                            </button>
                                        </div>
                                    </div>
                                @endif
                                @if(!$order->stripe_session_id && !$order->stripe_payment_intent)
                                    <div class="text-center py-4">
                                        <p class="text-sm text-gray-500">No Stripe payment information available</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Order Items</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Product
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Unit Price
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Quantity
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Total
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Files
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($order->orderItems as $item)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div>
                                                            <div class="text-sm font-medium text-gray-900">
                                                                {{ $item->product->title }}
                                                            </div>
                                                            @if($item->product->description)
                                                                <div class="text-sm text-gray-500">
                                                                    {{ Str::limit($item->product->description, 50) }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">¥{{ number_format($item->unit_price_cents / 100) }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">{{ $item->qty }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">¥{{ number_format($item->line_total_cents / 100) }}</div>
                                                </td>
                                                <td class="px-6 py-4">
                                                    @if($item->product->files->count() > 0)
                                                        <div class="text-sm text-gray-900">
                                                            @foreach($item->product->files as $file)
                                                                <div class="mb-1">
                                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                                        {{ $file->original_name }}
                                                                    </span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <span class="text-sm text-gray-500">No files</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customer Information -->
                <div class="space-y-6">
                    <!-- Customer Details -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Customer Information</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Name</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $order->user->name }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Email</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $order->user->email }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Customer Since</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $order->user->created_at->format('M j, Y') }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Total Orders</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $order->user->orders->count() }}</p>
                                </div>
                                <div class="pt-4">
                                    <a href="{{ route('admin.users.show', $order->user) }}" 
                                       class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                        View Customer Profile →
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Download Grants -->
                    @if($order->status === 'paid')
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 bg-white border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Download Grants</h3>
                                @php
                                    $grants = $order->user->downloadGrants()
                                        ->whereHas('file.products', function($q) use ($order) {
                                            $q->whereIn('products.id', $order->orderItems->pluck('product_id'));
                                        })
                                        ->with('file')
                                        ->get();
                                @endphp
                                @if($grants->count() > 0)
                                    <div class="space-y-3">
                                        @foreach($grants as $grant)
                                            <div class="border rounded-lg p-3">
                                                <div class="flex justify-between items-start">
                                                    <div>
                                                        <p class="text-sm font-medium text-gray-900">{{ $grant->file->original_name }}</p>
                                                        <p class="text-xs text-gray-500">
                                                            Downloads: {{ $grant->download_count }}/{{ $grant->download_limit }}
                                                        </p>
                                                        <p class="text-xs text-gray-500">
                                                            Expires: {{ $grant->expires_at->format('M j, Y') }}
                                                        </p>
                                                    </div>
                                                    <div>
                                                        @if($grant->expires_at->isFuture() && $grant->download_count < $grant->download_limit)
                                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                                Active
                                                            </span>
                                                        @else
                                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                                Expired
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-sm text-gray-500">No download grants found for this order.</p>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Order Timeline -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Order Timeline</h3>
                            <div class="flow-root">
                                <ul class="-mb-8">
                                    <li>
                                        <div class="relative pb-8">
                                            <div class="relative flex space-x-3">
                                                <div>
                                                    <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                                        <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        </svg>
                                                    </span>
                                                </div>
                                                <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                    <div>
                                                        <p class="text-sm text-gray-500">Order created</p>
                                                    </div>
                                                    <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                        {{ $order->created_at->format('M j, g:i A') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    @if($order->completed_at)
                                        <li>
                                            <div class="relative pb-8">
                                                <div class="relative flex space-x-3">
                                                    <div>
                                                        <span class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                                                            <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                            </svg>
                                                        </span>
                                                    </div>
                                                    <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                        <div>
                                                            <p class="text-sm text-gray-500">Order completed</p>
                                                        </div>
                                                        <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                            {{ $order->completed_at->format('M j, g:i A') }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    @endif
                                    @if($order->refunded_at)
                                        <li>
                                            <div class="relative">
                                                <div class="relative flex space-x-3">
                                                    <div>
                                                        <span class="h-8 w-8 rounded-full bg-red-500 flex items-center justify-center ring-8 ring-white">
                                                            <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                                <path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                                                            </svg>
                                                        </span>
                                                    </div>
                                                    <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                        <div>
                                                            <p class="text-sm text-gray-500">Order refunded</p>
                                                        </div>
                                                        <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                            {{ $order->refunded_at->format('M j, g:i A') }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                // You could add a toast notification here
                alert('Copied to clipboard!');
            }, function(err) {
                console.error('Could not copy text: ', err);
            });
        }
    </script>
</x-admin-layout>