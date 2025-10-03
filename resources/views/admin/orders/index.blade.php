<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Orders Management') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('admin.orders.export', request()->query()) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Export CSV
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Stats Overview -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex items-center">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-700">Total Orders</h3>
                                <p class="text-3xl font-bold text-blue-600">{{ $totalOrders }}</p>
                            </div>
                            <div class="text-blue-500">
                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex items-center">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-700">Total Revenue</h3>
                                <p class="text-3xl font-bold text-green-600">¥{{ number_format($totalRevenue / 100) }}</p>
                            </div>
                            <div class="text-green-500">
                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M12 2l3.09 6.26L22 9l-5 4.87L18.18 22 12 18.27 5.82 22 7 13.87 2 9l6.91-.74L12 2z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex items-center">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-700">Pending Orders</h3>
                                <p class="text-3xl font-bold text-yellow-600">{{ $pendingOrders }}</p>
                            </div>
                            <div class="text-yellow-500">
                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex items-center">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-700">Failed Orders</h3>
                                <p class="text-3xl font-bold text-red-600">{{ $failedOrders }}</p>
                            </div>
                            <div class="text-red-500">
                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search and Filter Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="GET" action="{{ route('admin.orders.index') }}" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
                            <!-- Search -->
                            <div class="lg:col-span-2">
                                <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                                <input type="text" 
                                       name="search" 
                                       id="search"
                                       value="{{ request('search') }}"
                                       placeholder="Search by customer, product, or Stripe ID..."
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <!-- Status Filter -->
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">All Status</option>
                                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                                    <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                                </select>
                            </div>

                            <!-- Date From -->
                            <div>
                                <label for="date_from" class="block text-sm font-medium text-gray-700">Date From</label>
                                <input type="date" 
                                       name="date_from" 
                                       id="date_from"
                                       value="{{ request('date_from') }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <!-- Date To -->
                            <div>
                                <label for="date_to" class="block text-sm font-medium text-gray-700">Date To</label>
                                <input type="date" 
                                       name="date_to" 
                                       id="date_to"
                                       value="{{ request('date_to') }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <!-- Submit Button -->
                            <div class="flex items-end">
                                <button type="submit" class="w-full bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                    Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Orders Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white">
                    @if($orders->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Order ID
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Customer
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Products
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Amount
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Stripe ID
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($orders as $order)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    #{{ $order->id }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div>
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ $order->user->name }}
                                                        </div>
                                                        <div class="text-sm text-gray-500">
                                                            {{ $order->user->email }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm text-gray-900">
                                                    @foreach($order->orderItems as $item)
                                                        <div class="mb-1">
                                                            {{ $item->product->title }} ({{ $item->qty }}x)
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    ¥{{ number_format($order->total_cents / 100) }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ strtoupper($order->currency) }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
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
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">
                                                    @if($order->stripe_session_id)
                                                        <div class="mb-1">
                                                            <span class="text-xs text-gray-500">Session:</span><br>
                                                            <code class="text-xs bg-gray-100 px-1 rounded">{{ Str::limit($order->stripe_session_id, 20) }}</code>
                                                        </div>
                                                    @endif
                                                    @if($order->stripe_payment_intent)
                                                        <div>
                                                            <span class="text-xs text-gray-500">Payment:</span><br>
                                                            <code class="text-xs bg-gray-100 px-1 rounded">{{ Str::limit($order->stripe_payment_intent, 20) }}</code>
                                                        </div>
                                                    @endif
                                                    @if(!$order->stripe_session_id && !$order->stripe_payment_intent)
                                                        <span class="text-xs text-gray-400">No Stripe ID</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">
                                                    {{ $order->created_at->format('M j, Y') }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $order->created_at->format('g:i A') }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex justify-end space-x-2">
                                                    <a href="{{ route('admin.orders.show', $order) }}" 
                                                       class="text-indigo-600 hover:text-indigo-900">
                                                        View
                                                    </a>
                                                    @if($order->status === 'pending')
                                                        <form method="POST" action="{{ route('admin.orders.mark-completed', $order) }}" class="inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" 
                                                                    class="text-green-600 hover:text-green-900"
                                                                    onclick="return confirm('Mark this order as completed?')">
                                                                Complete
                                                            </button>
                                                        </form>
                                                        <form method="POST" action="{{ route('admin.orders.mark-failed', $order) }}" class="inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" 
                                                                    class="text-red-600 hover:text-red-900"
                                                                    onclick="return confirm('Mark this order as failed?')">
                                                                Fail
                                                            </button>
                                                        </form>
                                                    @endif
                                                    @if($order->status === 'paid' && !$order->refunded_at)
                                                        <a href="{{ route('admin.orders.refund', $order) }}" 
                                                           class="text-orange-600 hover:text-orange-900">
                                                            Refund
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6">
                            {{ $orders->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No orders found</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                @if(request()->hasAny(['search', 'status', 'date_from', 'date_to']))
                                    Try adjusting your search criteria.
                                @else
                                    Orders will appear here once customers start making purchases.
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>