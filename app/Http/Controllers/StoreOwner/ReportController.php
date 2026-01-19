<?php

namespace App\Http\Controllers\StoreOwner;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Display sales reports
     */
    public function sales(Request $request)
    {
        $store = auth()->user()->store;

        $startDate = $request->input('start_date', now()->subMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        $query = $store->orders()
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        $totalSales = $query->sum('total');
        $totalOrders = $query->count();
        $averageOrderValue = $totalOrders > 0 ? $totalSales / $totalOrders : 0;

        // Daily sales
        $dailySales = $store->orders()
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->selectRaw('DATE(created_at) as date, SUM(total) as total, COUNT(*) as orders')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Payment method breakdown
        $paymentMethods = $store->orders()
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->selectRaw('payment_method, SUM(total) as total, COUNT(*) as orders')
            ->groupBy('payment_method')
            ->get();

        return view('store-owner.reports.sales', compact(
            'store',
            'totalSales',
            'totalOrders',
            'averageOrderValue',
            'dailySales',
            'paymentMethods',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Display inventory reports
     */
    public function inventory()
    {
        $store = auth()->user()->store;

        $products = $store->products()
            ->with('category')
            ->orderBy('stock_quantity')
            ->get();

        $lowStockProducts = $products->filter(fn($p) => $p->isLowStock());
        $outOfStockProducts = $products->filter(fn($p) => $p->track_inventory && $p->stock_quantity === 0);

        $totalProducts = $products->count();
        $totalStockValue = $products->sum(fn($p) => $p->price * $p->stock_quantity);

        return view('store-owner.reports.inventory', compact(
            'store',
            'products',
            'lowStockProducts',
            'outOfStockProducts',
            'totalProducts',
            'totalStockValue'
        ));
    }
}
