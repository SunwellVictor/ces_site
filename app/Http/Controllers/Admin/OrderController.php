<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of orders with search and filtering.
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'orderItems.product']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('stripe_payment_intent', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('orderItems.product', function ($productQuery) use ($search) {
                      $productQuery->where('title', 'like', "%{$search}%");
                  });
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Amount range filter
        if ($request->filled('amount_min')) {
            $query->where('total_cents', '>=', $request->amount_min * 100);
        }
        if ($request->filled('amount_max')) {
            $query->where('total_cents', '<=', $request->amount_max * 100);
        }

        $orders = $query->latest()->paginate(20);

        // Calculate summary statistics
        $totalOrders = Order::count();
        $totalRevenue = Order::where('status', 'paid')->sum('total_cents');
        $pendingOrders = Order::where('status', 'pending')->count();
        $failedOrders = Order::where('status', 'failed')->count();

        return view('admin.orders.index', compact(
            'orders', 
            'totalOrders', 
            'totalRevenue', 
            'pendingOrders', 
            'failedOrders'
        ));
    }

    /**
     * Display the specified order with full details.
     */
    public function show(Order $order)
    {
        $order->load(['user', 'orderItems.product.files']);
        
        return view('admin.orders.show', compact('order'));
    }

    /**
     * Process a refund for the specified order (optional future feature).
     */
    public function refund(Request $request, Order $order)
    {
        // Validate that order can be refunded
        if ($order->status !== 'completed') {
            return redirect()->route('admin.orders.show', $order)
                            ->with('error', 'Only completed orders can be refunded.');
        }

        if ($order->refunded_at) {
            return redirect()->route('admin.orders.show', $order)
                            ->with('error', 'Order has already been refunded.');
        }

        $validated = $request->validate([
            'refund_reason' => ['required', 'string', 'max:500'],
            'refund_amount' => ['nullable', 'numeric', 'min:0', 'max:' . ($order->total_cents / 100)],
        ]);

        try {
            // TODO: Implement Stripe refund logic here
            // $stripe = new \Stripe\StripeClient(config('stripe.secret'));
            // $refund = $stripe->refunds->create([
            //     'payment_intent' => $order->stripe_payment_intent,
            //     'amount' => $validated['refund_amount'] ? $validated['refund_amount'] * 100 : null,
            //     'reason' => 'requested_by_customer',
            // ]);

            // Update order with refund information
            $order->update([
                'status' => 'refunded',
            ]);

            return redirect()->route('admin.orders.show', $order)
                            ->with('success', 'Order refunded successfully.');

        } catch (\Exception $e) {
            return redirect()->route('admin.orders.show', $order)
                            ->with('error', 'Refund failed: ' . $e->getMessage());
        }
    }

    /**
     * Mark order as paid manually.
     */
    public function markCompleted(Order $order)
    {
        if ($order->status === 'paid') {
            return redirect()->route('admin.orders.show', $order)
                            ->with('error', 'Order is already paid.');
        }

        $order->update([
            'status' => 'paid',
        ]);

        return redirect()->route('admin.orders.show', $order)
                        ->with('success', 'Order marked as paid.');
    }

    /**
     * Mark order as failed manually.
     */
    public function markFailed(Order $order)
    {
        if ($order->status === 'failed') {
            return redirect()->route('admin.orders.show', $order)
                            ->with('error', 'Order is already marked as failed.');
        }

        $order->update([
            'status' => 'failed',
        ]);

        return redirect()->route('admin.orders.show', $order)
                        ->with('success', 'Order marked as failed.');
    }

    /**
     * Export orders to CSV.
     */
    public function export(Request $request)
    {
        $query = Order::with(['user', 'orderItems.product']);

        // Apply same filters as index
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('stripe_payment_intent', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('orderItems.product', function ($productQuery) use ($search) {
                      $productQuery->where('title', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->latest()->get();

        $filename = 'orders_export_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($orders) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'Order ID',
                'Customer Name',
                'Customer Email',
                'Products',
                'Amount',
                'Status',
                'Payment Intent ID',
                'Created At',
            ]);

            // CSV data
            foreach ($orders as $order) {
                $products = $order->orderItems->map(function($item) {
                    return $item->product->title . ' (x' . $item->qty . ')';
                })->implode(', ');
                
                fputcsv($file, [
                    $order->id,
                    $order->user->name,
                    $order->user->email,
                    $products,
                    '$' . number_format($order->total_cents / 100, 2),
                    ucfirst($order->status),
                    $order->stripe_payment_intent,
                    $order->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
