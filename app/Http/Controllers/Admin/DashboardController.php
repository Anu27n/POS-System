<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Show the admin dashboard
     */
    public function index()
    {
        $stats = [
            'total_stores' => Store::count(),
            'active_stores' => Store::where('status', 'active')->count(),
            'total_users' => User::count(),
            'total_orders' => Order::count(),
            'total_revenue' => Order::where('payment_status', 'paid')->sum('total'),
            'pending_orders' => Order::where('order_status', 'pending')->count(),
        ];

        $recentOrders = Order::with(['customer', 'store'])
            ->latest()
            ->take(10)
            ->get();

        $recentStores = Store::with('owner')
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentOrders', 'recentStores'));
    }
}
