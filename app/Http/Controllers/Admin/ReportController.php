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
        $storeId = $request->input('store_id');
        $status = $request->input('status');
        $paymentStatus = $request->input('payment_status');

        $query = Order::with('store', 'user')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        if ($status) {
            $query->where('order_status', $status);
        }

        if ($paymentStatus) {
            $query->where('payment_status', $paymentStatus);
        }

        $orders = $query->latest()->paginate(20)->withQueryString();

        $completedCount = Order::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->when($storeId, fn($q) => $q->where('store_id', $storeId))
            ->where('order_status', 'completed')
            ->count();

        $pendingCount = Order::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->when($storeId, fn($q) => $q->where('store_id', $storeId))
            ->where('order_status', 'pending')
            ->count();

        $cancelledCount = Order::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->when($storeId, fn($q) => $q->where('store_id', $storeId))
            ->where('order_status', 'cancelled')
            ->count();

        $ordersByStatus = Order::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->when($storeId, fn($q) => $q->where('store_id', $storeId))
            ->selectRaw('order_status, COUNT(*) as count')
            ->groupBy('order_status')
            ->get();

        $ordersByPaymentStatus = Order::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->when($storeId, fn($q) => $q->where('store_id', $storeId))
            ->selectRaw('payment_status, COUNT(*) as count')
            ->groupBy('payment_status')
            ->get();

        $stores = Store::orderBy('name')->get();

        // Export to CSV if requested
        if ($request->input('export') === 'csv') {
            return $this->exportOrdersCsv($query->get(), $startDate, $endDate);
        }

        return view('admin.reports.orders', compact(
            'orders',
            'ordersByStatus',
            'ordersByPaymentStatus',
            'completedCount',
            'pendingCount',
            'cancelledCount',
            'stores',
            'startDate',
            'endDate',
            'storeId',
            'status',
            'paymentStatus'
        ));
    }

    /**
     * Export orders to CSV
     */
    protected function exportOrdersCsv($orders, $startDate, $endDate)
    {
        $filename = "orders_report_{$startDate}_to_{$endDate}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($orders) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Order ID', 'Store', 'Customer', 'Total', 'Payment Status', 'Order Status', 'Date']);

            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->order_number,
                    $order->store->name ?? 'N/A',
                    $order->user->name ?? $order->customer_name ?? 'Guest',
                    $order->total,
                    $order->payment_status,
                    $order->order_status,
                    $order->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
