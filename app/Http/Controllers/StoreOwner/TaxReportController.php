<?php

namespace App\Http\Controllers\StoreOwner;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderTax;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaxReportController extends Controller
{
    /**
     * Display tax reports
     */
    public function index(Request $request)
    {
        $store = auth()->user()->getEffectiveStore();
        $period = $request->input('period', 'month');

        // Get date range
        $dates = $this->getDateRange($period, $request);
        $startDate = $dates['start'];
        $endDate = $dates['end'];

        // Get tax summary
        $taxSummary = OrderTax::whereHas('order', function ($query) use ($store, $startDate, $endDate) {
            $query->where('store_id', $store->id)
                ->where('payment_status', 'paid')
                ->whereBetween('created_at', [$startDate, $endDate]);
        })
            ->select('tax_name', 'tax_percentage')
            ->selectRaw('SUM(taxable_amount) as total_taxable')
            ->selectRaw('SUM(tax_amount) as total_tax')
            ->selectRaw('COUNT(*) as transaction_count')
            ->groupBy('tax_name', 'tax_percentage')
            ->get();

        // Get total tax collected
        $totalTax = $taxSummary->sum('total_tax');
        $totalTaxable = $taxSummary->sum('total_taxable');

        // Get order count with tax in the period
        $orderCount = Order::where('store_id', $store->id)
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereHas('taxes')
            ->count();

        // Get total sales for the period
        $totalSales = Order::where('store_id', $store->id)
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total');

        // Calculate effective tax rate
        $effectiveRate = $totalSales > 0 ? round(($totalTax / $totalSales) * 100, 2) : 0;

        // Build summary array for the view
        $summary = [
            'total_sales' => $totalSales,
            'total_tax' => $totalTax,
            'order_count' => $orderCount,
            'effective_rate' => $effectiveRate,
        ];

        // Get tax breakdown by type
        $taxBreakdown = OrderTax::whereHas('order', function ($query) use ($store, $startDate, $endDate) {
            $query->where('store_id', $store->id)
                ->where('payment_status', 'paid')
                ->whereBetween('created_at', [$startDate, $endDate]);
        })
            ->select('tax_name', 'tax_percentage as percentage')
            ->selectRaw('SUM(tax_amount) as total_amount')
            ->groupBy('tax_name', 'tax_percentage')
            ->orderByDesc('total_amount')
            ->get();

        // Get daily breakdown
        $dailyBreakdown = OrderTax::whereHas('order', function ($query) use ($store, $startDate, $endDate) {
            $query->where('store_id', $store->id)
                ->where('payment_status', 'paid')
                ->whereBetween('created_at', [$startDate, $endDate]);
        })
            ->join('orders', 'order_taxes.order_id', '=', 'orders.id')
            ->selectRaw('DATE(orders.created_at) as date')
            ->selectRaw('SUM(order_taxes.tax_amount) as total_tax')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Quick stats
        $stats = [
            'today' => $this->getTaxForPeriod($store->id, 'today'),
            'yesterday' => $this->getTaxForPeriod($store->id, 'yesterday'),
            'week' => $this->getTaxForPeriod($store->id, 'week'),
            'month' => $this->getTaxForPeriod($store->id, 'month'),
            'quarter' => $this->getTaxForPeriod($store->id, 'quarter'),
            'year' => $this->getTaxForPeriod($store->id, 'year'),
        ];

        return view('store-owner.reports.tax', compact(
            'store',
            'taxSummary',
            'totalTax',
            'totalTaxable',
            'dailyBreakdown',
            'stats',
            'period',
            'startDate',
            'endDate',
            'summary',
            'taxBreakdown'
        ));
    }

    /**
     * Get date range based on period
     */
    private function getDateRange(string $period, Request $request): array
    {
        $now = Carbon::now();

        return match ($period) {
            'today' => [
                'start' => $now->copy()->startOfDay(),
                'end' => $now->copy()->endOfDay(),
            ],
            'yesterday' => [
                'start' => $now->copy()->subDay()->startOfDay(),
                'end' => $now->copy()->subDay()->endOfDay(),
            ],
            'week' => [
                'start' => $now->copy()->startOfWeek(),
                'end' => $now->copy()->endOfWeek(),
            ],
            'month' => [
                'start' => $now->copy()->startOfMonth(),
                'end' => $now->copy()->endOfMonth(),
            ],
            'quarter' => [
                'start' => $now->copy()->startOfQuarter(),
                'end' => $now->copy()->endOfQuarter(),
            ],
            'year' => [
                'start' => $now->copy()->startOfYear(),
                'end' => $now->copy()->endOfYear(),
            ],
            'custom' => [
                'start' => Carbon::parse($request->input('start_date', $now->copy()->startOfMonth())),
                'end' => Carbon::parse($request->input('end_date', $now))->endOfDay(),
            ],
            default => [
                'start' => $now->copy()->startOfMonth(),
                'end' => $now->copy()->endOfMonth(),
            ],
        };
    }

    /**
     * Get tax collected for a specific period
     */
    private function getTaxForPeriod(int $storeId, string $period): float
    {
        $now = Carbon::now();

        $query = OrderTax::whereHas('order', function ($q) use ($storeId) {
            $q->where('store_id', $storeId)->where('payment_status', 'paid');
        });

        match ($period) {
            'today' => $query->whereHas('order', fn($q) => $q->whereDate('created_at', today())),
            'yesterday' => $query->whereHas('order', fn($q) => $q->whereDate('created_at', today()->subDay())),
            'week' => $query->whereHas('order', fn($q) => $q->whereBetween('created_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()])),
            'month' => $query->whereHas('order', fn($q) => $q->whereBetween('created_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])),
            'quarter' => $query->whereHas('order', fn($q) => $q->whereBetween('created_at', [$now->copy()->startOfQuarter(), $now->copy()->endOfQuarter()])),
            'year' => $query->whereHas('order', fn($q) => $q->whereBetween('created_at', [$now->copy()->startOfYear(), $now->copy()->endOfYear()])),
            default => null,
        };

        return $query->sum('tax_amount');
    }

    /**
     * Export tax report
     */
    public function export(Request $request)
    {
        $store = auth()->user()->getEffectiveStore();
        $period = $request->input('period', 'month');
        $dates = $this->getDateRange($period, $request);

        $orders = Order::with('taxes')
            ->where('store_id', $store->id)
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$dates['start'], $dates['end']])
            ->orderBy('created_at')
            ->get();

        $filename = 'tax_report_' . $dates['start']->format('Y-m-d') . '_to_' . $dates['end']->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($orders) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Order #', 'Date', 'Subtotal', 'Tax Name', 'Tax %', 'Taxable Amount', 'Tax Amount', 'Total']);

            foreach ($orders as $order) {
                foreach ($order->taxes as $tax) {
                    fputcsv($file, [
                        $order->order_number,
                        $order->created_at->format('Y-m-d H:i'),
                        $order->subtotal,
                        $tax->tax_name,
                        $tax->tax_percentage . '%',
                        $tax->taxable_amount,
                        $tax->tax_amount,
                        $order->total,
                    ]);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
