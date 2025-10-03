<x-app-layout>
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900">My Orders</h1>
                <a href="{{ route('account.dashboard') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                    ← Back to Dashboard
                </a>
            </div>

            <!-- Order Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="text-center">
                        <p class="text-2xl font-bold text-gray-900">{{ $orderStats['total'] }}</p>
                        <p class="text-sm text-gray-600">Total Orders</p>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="text-center">
                        <p class="text-2xl font-bold text-green-600">{{ $orderStats['completed'] }}</p>
                        <p class="text-sm text-gray-600">Completed</p>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="text-center">
                        <p class="text-2xl font-bold text-yellow-600">{{ $orderStats['pending'] }}</p>
                        <p class="text-sm text-gray-600">Pending</p>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="text-center">
                        <p class="text-2xl font-bold text-red-600">{{ $orderStats['cancelled'] }}</p>
                        <p class="text-sm text-gray-600">Cancelled</p>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <form method="GET" action="{{ route('account.orders') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" id="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                        </select>
                    </div>
                    <div>
                        <label for="from_date" class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
                        <input type="date" name="from_date" id="from_date" value="{{ request('from_date') }}" 
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="to_date" class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
                        <input type="date" name="to_date" id="to_date" value="{{ request('to_date') }}" 
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md">
                            Filter
                        </button>
                    </div>
                </form>
            </div>

            <!-- Orders Table -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                @if($orders->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($orders as $order)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">#{{ $order->id }}</div>
                                            @if($order->stripe_payment_intent)
                                                <div class="text-xs text-gray-500">{{ substr($order->stripe_payment_intent, 0, 20) }}...</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $order->created_at->format('M j, Y') }}
                                            <div class="text-xs text-gray-500">{{ $order->created_at->format('g:i A') }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $order->orderItems->count() }} item(s)
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ currency($order->total_cents) }}
                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                                @if($order->status === 'completed') bg-green-100 text-green-800
                                                @elseif($order->status === 'pending') bg-yellow-100 text-yellow-800
                                                @elseif($order->status === 'failed') bg-red-100 text-red-800
                                                @elseif($order->status === 'cancelled') bg-gray-100 text-gray-800
                                                @else bg-blue-100 text-blue-800 @endif">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('account.orders.show', $order) }}" 
                                               class="text-blue-600 hover:text-blue-900">View Details</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $orders->links() }}
                    </div>
                @else
                    <!-- Enhanced Empty State -->
                    <div class="text-center py-16">
                        <!-- Icon -->
                        <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-6">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                        </div>
                        
                        <!-- Title and Message -->
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">
                            @if(request()->hasAny(['status', 'from_date', 'to_date']))
                                No orders match your filters
                            @else
                                No orders yet
                            @endif
                        </h3>
                        
                        <p class="text-gray-600 mb-6 max-w-md mx-auto">
                            @if(request()->hasAny(['status', 'from_date', 'to_date']))
                                We couldn't find any orders matching your filter criteria. Try adjusting your filters or view all orders.
                            @else
                                You haven't placed any orders yet. Start shopping to see your order history here!
                            @endif
                        </p>
                        
                        <!-- Action Buttons -->
                        <div class="space-y-3">
                            @if(request()->hasAny(['status', 'from_date', 'to_date']))
                                <a href="{{ route('account.orders') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-6 rounded-lg transition-colors">
                                    View All Orders
                                </a>
                            @else
                                <a href="{{ route('products.index') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-6 rounded-lg transition-colors">
                                    Browse Products
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>