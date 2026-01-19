@extends('layouts.admin')

@section('title', 'User Details')
@section('page-title', $user->name)

@section('content')
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="bg-secondary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                     style="width: 100px; height: 100px; font-size: 2.5rem;">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <h4>{{ $user->name }}</h4>
                @php
                    $roleColors = [
                        'admin' => 'danger',
                        'store_owner' => 'primary',
                        'customer' => 'secondary',
                    ];
                @endphp
                <span class="badge bg-{{ $roleColors[$user->role] ?? 'secondary' }} mb-2">
                    {{ str_replace('_', ' ', ucfirst($user->role)) }}
                </span>
                <span class="badge bg-{{ $user->is_active ? 'success' : 'secondary' }}">
                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
            <div class="card-footer bg-white">
                <div class="row text-center">
                    <div class="col-6 border-end">
                        <div class="h5 mb-0">{{ $user->orders->count() }}</div>
                        <small class="text-muted">Orders</small>
                    </div>
                    <div class="col-6">
                        <div class="h5 mb-0">${{ number_format($user->orders->where('payment_status', 'paid')->sum('total'), 0) }}</div>
                        <small class="text-muted">Spent</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h6 class="mb-0">User Information</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted d-block">Email</small>
                    <strong>{{ $user->email }}</strong>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Phone</small>
                    <strong>{{ $user->phone ?? '-' }}</strong>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Address</small>
                    <strong>{{ $user->address ?? '-' }}</strong>
                </div>
                <div class="mb-0">
                    <small class="text-muted d-block">Joined</small>
                    <strong>{{ $user->created_at->format('M d, Y') }}</strong>
                </div>
            </div>
        </div>

        @if($user->store)
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="mb-0">Owned Store</h6>
            </div>
            <div class="card-body">
                <a href="{{ route('admin.stores.show', $user->store) }}" class="text-decoration-none">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" 
                             style="width: 50px; height: 50px;">
                            {{ strtoupper(substr($user->store->name, 0, 1)) }}
                        </div>
                        <div>
                            <strong>{{ $user->store->name }}</strong>
                            <div class="text-muted small">{{ ucfirst($user->store->type) }}</div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Recent Orders</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Store</th>
                                <th>Total</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($user->orders as $order)
                            <tr>
                                <td><code>{{ $order->order_number }}</code></td>
                                <td>{{ $order->store->name ?? 'N/A' }}</td>
                                <td>${{ number_format($order->total, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : 'warning' }}">
                                        {{ ucfirst($order->payment_status) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ ucfirst($order->order_status) }}</span>
                                </td>
                                <td>{{ $order->created_at->format('M d, Y') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No orders yet</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
