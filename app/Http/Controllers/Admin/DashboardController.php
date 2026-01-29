<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Store;
use App\Models\User;
use App\Models\Subscription;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Show the admin dashboard
     */
    public function index()
    {
        // Subscription-based stats (admin revenue comes from subscriptions)
        $subscriptionStats = [
            'total_subscriptions' => Subscription::count(),
            'active_subscriptions' => Subscription::where('status', 'active')
                ->orWhere(function ($query) {
                    $query->where('status', 'trial')
                        ->where('trial_ends_at', '>', now());
                })->count(),
            'subscription_revenue' => Subscription::where('status', 'active')
                ->whereNotNull('amount_paid')
                ->sum('amount_paid'),
        ];

        $stats = [
            'total_stores' => Store::count(),
            'active_stores' => Store::where('status', 'active')->count(),
            'total_users' => User::count(),
            'total_orders' => $subscriptionStats['total_subscriptions'], // Based on subscriptions
            'total_revenue' => $subscriptionStats['subscription_revenue'], // Based on subscriptions
            'pending_orders' => Subscription::where('status', 'trial')->count(), // Trial subscriptions
            'active_subscriptions' => $subscriptionStats['active_subscriptions'],
        ];

        // Recent subscriptions instead of store orders
        $recentSubscriptions = Subscription::with(['store', 'plan'])
            ->latest()
            ->take(10)
            ->get();

        $recentStores = Store::with('owner')
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentSubscriptions', 'recentStores'));
    }
}
