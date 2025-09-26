<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\Post;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard with key metrics and overview.
     */
    public function index()
    {
        // Get key metrics for dashboard overview
        $metrics = [
            'total_users' => User::count(),
            'new_users_this_month' => User::whereMonth('created_at', now()->month)
                                         ->whereYear('created_at', now()->year)
                                         ->count(),
            'total_products' => Product::count(),
            'active_products' => Product::where('is_active', true)->count(),
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'completed_orders' => Order::where('status', 'completed')->count(),
            'total_revenue' => Order::where('status', 'completed')->sum('total_amount_cents') / 100,
            'revenue_this_month' => Order::where('status', 'completed')
                                        ->whereMonth('created_at', now()->month)
                                        ->whereYear('created_at', now()->year)
                                        ->sum('total_amount_cents') / 100,
            'total_posts' => Post::count(),
            'published_posts' => Post::where('status', 'published')->count(),
            'draft_posts' => Post::where('status', 'draft')->count(),
            'total_files' => File::count(),
        ];

        // Get recent activity
        $recent_orders = Order::with(['user', 'orderItems.product'])
                             ->latest()
                             ->limit(5)
                             ->get();

        $recent_users = User::with('roles')
                           ->latest()
                           ->limit(5)
                           ->get();

        $recent_posts = Post::with('author')
                           ->latest()
                           ->limit(5)
                           ->get();

        return view('admin.dashboard', compact('metrics', 'recent_orders', 'recent_users', 'recent_posts'));
    }
}
