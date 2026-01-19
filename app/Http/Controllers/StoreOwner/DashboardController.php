<?php

namespace App\Http\Controllers\StoreOwner;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Show the store owner dashboard
     */
    public function index()
    {
        $store = auth()->user()->store;

        if (!$store) {
            return view('store-owner.no-store');
        }

        $stats = [
            'total_products' => $store->products()->count(),
            'available_products' => $store->products()->where('status', 'available')->count(),
            'low_stock_products' => $store->products()->where('track_inventory', true)
                ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')->count(),
            'total_orders' => $store->orders()->count(),
            'pending_orders' => $store->orders()->where('order_status', 'pending')->count(),
            'today_orders' => $store->orders()->whereDate('created_at', today())->count(),
            'today_revenue' => $store->orders()->whereDate('created_at', today())
                ->where('payment_status', 'paid')->sum('total'),
            'total_revenue' => $store->orders()->where('payment_status', 'paid')->sum('total'),
        ];

        $recentOrders = $store->orders()
            ->with('customer')
            ->latest()
            ->take(10)
            ->get();

        $lowStockProducts = $store->products()
            ->where('track_inventory', true)
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->take(5)
            ->get();

        return view('store-owner.dashboard', compact('store', 'stats', 'recentOrders', 'lowStockProducts'));
    }
}
