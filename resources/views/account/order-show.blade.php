<x-app-layout>
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Order #{{ $order->id }}</h1>
                <a href="{{ route('account.orders') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                    ← Back to Orders
                </a>
            </div>

            <!-- Order Summary -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Order Information</h3>
                        <dl class="space-y-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-600">Order Date</dt>
                                <dd class="text-sm text-gray-900">{{ $order->created_at->format('F j, Y \a\t g:i A') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-600">Status</dt>
                                <dd class="text-sm">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                        @if($order->status === 'completed') bg-green-100 text-green-800
                                        @elseif($order->status === 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($order->status === 'failed') bg-red-100 text-red-800
                                        @elseif($order->status === 'cancelled') bg-gray-100 text-gray-800
                                        @else bg-blue-100 text-blue-800 @endif">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-600">Currency</dt>
                                <dd class="text-sm text-gray-900">{{ strtoupper($order->currency) }}</dd>
                            </div>
                        </dl>
                    </div>

                    @if($order->stripe_payment_intent || $order->stripe_session_id)
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">Payment Information</h3>
                            <dl class="space-y-2">
                                @if($order->stripe_payment_intent)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-600">Payment Intent</dt>
                                        <dd class="text-sm text-gray-900 font-mono">{{ $order->stripe_payment_intent }}</dd>
                                    </div>
                                @endif
                                @if($order->stripe_session_id)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-600">Session ID</dt>
                                        <dd class="text-sm text-gray-900 font-mono">{{ $order->stripe_session_id }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    @endif

                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Order Total</h3>
                        <div class="text-3xl font-bold text-gray-900">{{ format_yen($order->total_cents) }}</div>
                        @if($order->status === 'completed')
                            <p class="text-sm text-green-600 mt-1">✓ Payment completed</p>
                        @elseif($order->status === 'pending')
                            <p class="text-sm text-yellow-600 mt-1">⏳ Payment pending</p>
                        @elseif($order->status === 'failed')
                            <p class="text-sm text-red-600 mt-1">✗ Payment failed</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Order Items</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Line Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($order->orderItems as $item)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $item->product_name }}</div>
                                                @if($item->product)
                                                    <div class="text-sm text-gray-500">
                                                        <a href="{{ route('products.show', $item->product) }}" 
                                                           class="text-blue-600 hover:text-blue-800">View Product</a>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $item->quantity }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ format_yen($item->unit_price_cents) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ format_yen($item->quantity * $item->unit_price_cents) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                                    Order Total:
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                    {{ format_yen($order->total_cents) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Download Grants -->
            @if($order->downloadGrants->count() > 0)
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Available Downloads</h3>
                    <div class="space-y-4">
                        @foreach($order->downloadGrants as $grant)
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div>
                                    <div class="font-medium text-gray-900">{{ $grant->product->name }}</div>
                                    <div class="text-sm text-gray-600">
                                        {{ remaining_attempts($grant) }} of {{ $grant->max_downloads }} downloads remaining
                                        @if($grant->expires_at)
                                            • Expires {{ $grant->expires_at->format('M j, Y') }}
                                        @endif
                                    </div>
                                </div>
                                <div>
                                    @if($grant->isValid())
                                        <a href="{{ route('downloads.index') }}" 
                                           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                            Go to Downloads
                                        </a>
                                    @else
                                        <span class="bg-gray-100 text-gray-500 px-4 py-2 rounded-md text-sm">
                                            @if($grant->expires_at && $grant->expires_at < now())
                                                Expired
                                            @else
                                                No downloads remaining
                                            @endif
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>