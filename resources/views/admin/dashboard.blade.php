@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard Overview')

@section('content')
<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="stat-label">Total Stores</div>
                <div class="stat-value">{{ $stats['total_stores'] }}</div>
                <small class="text-success">{{ $stats['active_stores'] }} active</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card" style="border-left-color: #059669;">
            <div class="card-body">
                <div class="stat-label">Total Users</div>
                <div class="stat-value">{{ $stats['total_users'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card" style="border-left-color: #d97706;">
            <div class="card-body">
                <div class="stat-label">Total Subscriptions</div>
                <div class="stat-value">{{ $stats['total_orders'] }}</div>
                <small class="text-success">{{ $stats['active_subscriptions'] ?? 0 }} active</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card" style="border-left-color: #7c3aed;">
            <div class="card-body">
                <div class="stat-label">Subscription Revenue</div>
                <div class="stat-value">₹{{ number_format($stats['total_revenue'], 2) }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Subscriptions -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Recent Subscriptions</h6>
                <a href="{{ route('admin.stores.index') }}" class="btn btn-sm btn-outline-primary">View All Stores</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Store</th>
                                <th>Plan</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentSubscriptions as $subscription)
                            <tr>
                                <td>{{ $subscription->store->name ?? 'N/A' }}</td>
                                <td><span class="badge bg-primary">{{ $subscription->plan->name ?? 'N/A' }}</span></td>
                                <td>₹{{ number_format($subscription->amount_paid, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $subscription->status === 'active' ? 'success' : ($subscription->status === 'pending' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($subscription->status) }}
                                    </span>
                                </td>
                                <td>{{ $subscription->starts_at ? $subscription->starts_at->format('M d, Y') : 'N/A' }}</td>
                                <td>{{ $subscription->ends_at ? $subscription->ends_at->format('M d, Y') : 'N/A' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No subscriptions yet</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Stores -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Recent Stores</h6>
                <a href="{{ route('admin.stores.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                @forelse($recentStores as $store)
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" 
                         style="width: 40px; height: 40px;">
                        {{ strtoupper(substr($store->name, 0, 1)) }}
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold">{{ $store->name }}</div>
                        <small class="text-muted">{{ $store->owner->name ?? 'No owner' }}</small>
                    </div>
                    <span class="badge bg-{{ $store->status === 'active' ? 'success' : 'secondary' }}">
                        {{ ucfirst($store->status) }}
                    </span>
                </div>
                @empty
                <p class="text-muted text-center mb-0">No stores yet</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

