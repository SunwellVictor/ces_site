<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    /**
     * Display the user's account dashboard.
     */
    public function dashboard()
    {
        /** @var User $user */
        $user = Auth::user();
        
        // Get recent orders
        $recentOrders = Order::where('user_id', $user->id)
            ->with(['orderItems.product'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get download grants
        $downloadGrants = $user->downloadGrants()
            ->with('product')
            ->whereRaw('downloads_used < max_downloads')
            ->where(function($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate stats
        $stats = [
            'total_orders' => Order::where('user_id', $user->id)->count(),
            'completed_orders' => Order::where('user_id', $user->id)
                ->where('status', 'paid')->count(),
            'total_spent' => Order::where('user_id', $user->id)
                ->where('status', 'paid')
                ->sum('total_cents'),
            'available_downloads' => $downloadGrants->count(),
        ];

        return view('account.dashboard', compact('user', 'recentOrders', 'downloadGrants', 'stats'));
    }

    /**
     * Display the user's order history.
     */
    public function orders(Request $request)
    {
        $user = Auth::user();
        
        $query = Order::where('user_id', $user->id)
            ->with(['orderItems.product']);

        // Filter by status if provided
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by date range if provided
        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $orders = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        // Get order statistics
        $orderStats = [
            'total' => Order::where('user_id', $user->id)->count(),
            'completed' => Order::where('user_id', $user->id)->where('status', 'paid')->count(),
            'pending' => Order::where('user_id', $user->id)->where('status', 'pending')->count(),
            'cancelled' => Order::where('user_id', $user->id)->where('status', 'cancelled')->count(),
        ];

        return view('account.orders', compact('orders', 'orderStats'));
    }

    /**
     * Show a specific order.
     */
    public function showOrder(Order $order)
    {
        // Ensure the order belongs to the authenticated user
        if ($order->user_id !== Auth::id()) {
            abort(404);
        }

        $order->load(['orderItems.product', 'downloadGrants.product']);

        return view('account.order-show', compact('order'));
    }

    /**
     * Update user profile information.
     */
    public function updateProfile(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        return redirect()->route('account.profile.edit')
            ->with('status', 'profile-updated');
    }

    /**
     * Show the profile edit form.
     */
    public function editProfile()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }
}
