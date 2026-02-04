<?php

namespace App\Http\Controllers\StoreOwner;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\RepairJob;
use App\Models\Staff;
use App\Services\QRCodeService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    protected QRCodeService $qrCodeService;

    public function __construct(QRCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
    }

    /**
     * Show the store owner dashboard
     */
    public function index()
    {
        $store = auth()->user()->getEffectiveStore();

        if (!$store) {
            return view('store-owner.no-store');
        }

        // ===== REPAIR JOB STATS =====
        
        // Open jobs (not delivered or cancelled)
        $openJobs = RepairJob::where('store_id', $store->id)
            ->whereNotIn('status', ['delivered', 'cancelled'])
            ->count();

        // Jobs due today
        $jobsDueToday = RepairJob::where('store_id', $store->id)
            ->whereNotIn('status', ['delivered', 'cancelled'])
            ->whereDate('expected_delivery_at', today())
            ->count();

        // Overdue jobs
        $overdueJobs = RepairJob::where('store_id', $store->id)
            ->whereNotIn('status', ['delivered', 'cancelled'])
            ->where('expected_delivery_at', '<', today())
            ->count();

        // Completed today
        $completedToday = RepairJob::where('store_id', $store->id)
            ->where('status', 'delivered')
            ->whereDate('updated_at', today())
            ->count();

        // Today's repair revenue
        $todayRepairRevenue = RepairJob::where('store_id', $store->id)
            ->where('status', 'delivered')
            ->whereDate('updated_at', today())
            ->sum('final_cost') ?? 0;

        // This month's repair revenue
        $monthRepairRevenue = RepairJob::where('store_id', $store->id)
            ->where('status', 'delivered')
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->sum('final_cost') ?? 0;

        // Recent repair jobs
        $recentJobs = RepairJob::where('store_id', $store->id)
            ->with(['customer', 'technician'])
            ->latest()
            ->take(10)
            ->get();

        // Technician workload
        $technicians = Staff::where('store_id', $store->id)
            ->where('role', 'technician')
            ->where('is_active', true)
            ->withCount(['assignedRepairJobs as active_jobs' => function ($query) {
                $query->whereNotIn('status', ['delivered', 'cancelled']);
            }])
            ->get();

        // Jobs by status for chart
        $jobsByStatus = RepairJob::where('store_id', $store->id)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // ===== SPARE PARTS / INVENTORY STATS =====
        
        $lowStockProducts = $store->products()
            ->where('track_inventory', true)
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->count();

        $lowStockItems = $store->products()
            ->where('track_inventory', true)
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->take(5)
            ->get();

        // ===== LEGACY POS STATS (kept for compatibility) =====
        
        $todaySales = $store->orders()
            ->whereDate('created_at', today())
            ->where('payment_status', 'paid')
            ->sum('total') ?? 0;

        $todayOrders = $store->orders()
            ->whereDate('created_at', today())
            ->count();

        $monthSales = $store->orders()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('payment_status', 'paid')
            ->sum('total') ?? 0;

        $monthOrders = $store->orders()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $pendingOrders = $store->orders()->where('order_status', 'pending')->count();
        $totalProducts = $store->products()->count();

        // Generate store QR code
        $storeUrl = route('store.show', $store->slug);
        $qrCode = $this->qrCodeService->generatePngDataUri($storeUrl);

        return view('store-owner.dashboard', compact(
            'store',
            // Repair Job Stats
            'openJobs',
            'jobsDueToday',
            'overdueJobs',
            'completedToday',
            'todayRepairRevenue',
            'monthRepairRevenue',
            'recentJobs',
            'technicians',
            'jobsByStatus',
            // Inventory Stats
            'lowStockProducts',
            'lowStockItems',
            'totalProducts',
            // Legacy POS Stats
            'todaySales',
            'todayOrders',
            'monthSales',
            'monthOrders',
            'pendingOrders',
            'qrCode'
        ));
    }
}
