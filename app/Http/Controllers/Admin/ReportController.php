<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Display sales reports
     */
    public function sales(Request $request)
    {
        $startDate = $request->input('start_date', now()->subMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $storeId = $request->input('store_id');

        $query = Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        $totalSales = $query->sum('total');
        $totalOrders = $query->count();
        $averageOrderValue = $totalOrders > 0 ? $totalSales / $totalOrders : 0;

        // Daily sales
        $dailySales = Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->when($storeId, fn($q) => $q->where('store_id', $storeId))
            ->selectRaw('DATE(created_at) as date, SUM(total) as total, COUNT(*) as orders')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Sales by store
        $salesByStore = Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->with('store')
            ->selectRaw('store_id, SUM(total) as total, COUNT(*) as orders')
            ->groupBy('store_id')
            ->orderByDesc('total')
            ->get();

        // Payment method breakdown
        $paymentMethods = Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->when($storeId, fn($q) => $q->where('store_id', $storeId))
            ->selectRaw('payment_method, SUM(total) as total, COUNT(*) as orders')
            ->groupBy('payment_method')
            ->get();

        $stores = Store::orderBy('name')->get();

        return view('admin.reports.sales', compact(
            'totalSales',
            'totalOrders',
            'averageOrderValue',
            'dailySales',
            'salesByStore',
            'paymentMethods',
            'stores',
            'startDate',
            'endDate',
            'storeId'
        ));
    }

    /**
     * Display order reports
     */
    public function orders(Request $request)
    {
        $startDate = $request->input('start_date', now()->subMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        $ordersByStatus = Order::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->selectRaw('order_status, COUNT(*) as count')
            ->groupBy('order_status')
            ->get();

        $ordersByPaymentStatus = Order::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->selectRaw('payment_status, COUNT(*) as count')
            ->groupBy('payment_status')
            ->get();

        return view('admin.reports.orders', compact(
            'ordersByStatus',
            'ordersByPaymentStatus',
            'startDate',
            'endDate'
        ));
    }
}
