<?php

namespace App\Http\Controllers\StoreOwner;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
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
        $store = auth()->user()->store;

        if (!$store) {
            return view('store-owner.no-store');
        }

        // Today's stats
        $todaySales = $store->orders()
            ->whereDate('created_at', today())
            ->where('payment_status', 'paid')
            ->sum('total') ?? 0;
        
        $todayOrders = $store->orders()
            ->whereDate('created_at', today())
            ->count();

        // This month's stats
        $monthSales = $store->orders()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('payment_status', 'paid')
            ->sum('total') ?? 0;
        
        $monthOrders = $store->orders()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Other stats
        $pendingOrders = $store->orders()->where('order_status', 'pending')->count();
        $totalProducts = $store->products()->count();
        $lowStockProducts = $store->products()
            ->where('track_inventory', true)
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->count();

        // Low stock items for the alert section
        $lowStockItems = $store->products()
            ->where('track_inventory', true)
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->take(5)
            ->get();

        // Recent orders
        $recentOrders = $store->orders()
            ->with(['user', 'items'])
            ->latest()
            ->take(10)
            ->get();

        // Generate store QR code as PNG base64 data URI
        $storeUrl = route('store.show', $store->slug);
        $qrCode = $this->qrCodeService->generatePngDataUri($storeUrl);

        return view('store-owner.dashboard', compact(
            'store',
            'todaySales',
            'todayOrders',
            'monthSales',
            'monthOrders',
            'pendingOrders',
            'totalProducts',
            'lowStockProducts',
            'lowStockItems',
            'recentOrders',
            'qrCode'
        ));
    }
}
