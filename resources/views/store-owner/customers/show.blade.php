@extends('layouts.store-owner')

@section('title', 'Customer Details')
@section('page-title', 'Customer Details')

@section('content')
<div class="row">
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-body text-center">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" 
                     style="width: 80px; height: 80px; font-size: 2rem;">
                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                </div>
                <h4 class="mb-1">{{ $customer->name }}</h4>
                @if($customer->is_manually_added)
                    <span class="badge bg-info">Manually Added</span>
                @else
                    <span class="badge bg-success">From Checkout</span>
                @endif
            </div>
            <hr class="my-0">
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    @if($customer->phone)
                    <li class="mb-2">
                        <i class="bi bi-phone me-2 text-muted"></i>
                        <a href="tel:{{ $customer->phone }}" class="text-decoration-none">{{ $customer->phone }}</a>
                    </li>
                    @endif
                    @if($customer->email)
                    <li class="mb-2">
                        <i class="bi bi-envelope me-2 text-muted"></i>
                        <a href="mailto:{{ $customer->email }}" class="text-decoration-none">{{ $customer->email }}</a>
                    </li>
                    @endif
                    @if($customer->address)
                    <li class="mb-2">
                        <i class="bi bi-geo-alt me-2 text-muted"></i>
                        {{ $customer->address }}
                    </li>
                    @endif
                    <li class="mb-2">
                        <i class="bi bi-calendar me-2 text-muted"></i>
                        Customer since {{ $customer->created_at->format('M d, Y') }}
                    </li>
                </ul>
            </div>
            <div class="card-footer">
                <a href="{{ route('store-owner.customers.edit', $customer) }}" class="btn btn-primary w-100">
                    <i class="bi bi-pencil me-1"></i> Edit Customer
                </a>
            </div>
        </div>

        @if($customer->notes)
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Notes</h6>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $customer->notes }}</p>
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-8">
        <!-- Stats -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="stat-value">{{ $customer->total_orders }}</div>
                        <div class="stat-label">Total Orders</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="stat-value">₹{{ number_format($customer->total_spent, 0) }}</div>
                        <div class="stat-label">Total Spent</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="stat-value">
                            @if($customer->total_orders > 0)
                                ₹{{ number_format($customer->total_spent / $customer->total_orders, 0) }}
                            @else
                                ₹0
                            @endif
                        </div>
                        <div class="stat-label">Avg. Order Value</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Recent Orders</h6>
            </div>
            <div class="card-body p-0">
                @if(count($orders) > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Order #</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Payment</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                            <tr>
                                <td class="ps-4">
                                    <a href="{{ route('store-owner.orders.show', $order) }}" class="text-decoration-none fw-semibold">
                                        {{ $order->order_number }}
                                    </a>
                                </td>
                                <td>{{ $order->created_at->format('M d, Y H:i') }}</td>
                                <td class="fw-semibold">₹{{ number_format($order->total, 2) }}</td>
                                <td>
                                    @if($order->payment_status === 'paid')
                                        <span class="badge bg-success">Paid</span>
                                    @else
                                        <span class="badge bg-warning">{{ ucfirst($order->payment_status) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ ucfirst($order->order_status) }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-5">
                    <i class="bi bi-receipt fs-1 text-muted d-block mb-3"></i>
                    <p class="text-muted mb-0">No orders yet</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="mt-4">
    <a href="{{ route('store-owner.customers.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Customers
    </a>
</div>
@endsection
