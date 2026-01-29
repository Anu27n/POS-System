@extends('layouts.admin')

@section('title', 'Store Details')
@section('page-title', $store->name)

@section('content')
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body text-center">
                @if($store->logo)
                    <img src="{{ asset('storage/' . $store->logo) }}" alt="{{ $store->name }}" 
                         class="rounded-circle mb-3" style="width: 100px; height: 100px; object-fit: cover;">
                @else
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                         style="width: 100px; height: 100px; font-size: 2.5rem;">
                        {{ strtoupper(substr($store->name, 0, 1)) }}
                    </div>
                @endif
                <h4>{{ $store->name }}</h4>
                <span class="badge bg-{{ $store->status === 'active' ? 'success' : 'secondary' }} mb-3">
                    {{ ucfirst($store->status) }}
                </span>
                <p class="text-muted">{{ $store->description ?? 'No description' }}</p>
            </div>
            <div class="card-footer bg-white">
                <div class="row text-center">
                    <div class="col-4 border-end">
                        <div class="h5 mb-0">{{ $store->products->count() }}</div>
                        <small class="text-muted">Products</small>
                    </div>
                    <div class="col-4 border-end">
                        <div class="h5 mb-0">{{ $store->orders->count() }}</div>
                        <small class="text-muted">Orders</small>
                    </div>
                    <div class="col-4">
                        <div class="h5 mb-0">₹{{ number_format($store->orders->where('payment_status', 'paid')->sum('total'), 0) }}</div>
                        <small class="text-muted">Revenue</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h6 class="mb-0">Store Information</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted d-block">Owner</small>
                    <strong>{{ $store->owner->name ?? 'Not assigned' }}</strong>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Type</small>
                    <strong>{{ ucfirst($store->type) }}</strong>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Email</small>
                    <strong>{{ $store->email ?? '-' }}</strong>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Phone</small>
                    <strong>{{ $store->phone ?? '-' }}</strong>
                </div>
                <div class="mb-0">
                    <small class="text-muted d-block">Address</small>
                    <strong>{{ $store->address ?? '-' }}</strong>
                </div>
            </div>
        </div>
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
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($store->orders as $order)
                            <tr>
                                <td><code>{{ $order->order_number }}</code></td>
                                <td>{{ $order->customer->name ?? 'Guest' }}</td>
                                <td>₹{{ number_format($order->total, 2) }}</td>
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

