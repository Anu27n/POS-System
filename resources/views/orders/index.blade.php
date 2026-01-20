@extends('layouts.app')

@section('title', 'My Orders')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">My Orders</h1>

    @if($orders->count() > 0)
    <div class="row g-4">
        @foreach($orders as $order)
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Order #{{ $order->order_number }}</h5>
                        <small class="text-muted">{{ $order->created_at->format('M d, Y H:i') }}</small>
                    </div>
                    <div class="text-end">
                        @switch($order->status)
                        @case('pending')
                        <span class="badge bg-warning fs-6">Pending</span>
                        @break
                        @case('confirmed')
                        <span class="badge bg-info fs-6">Confirmed</span>
                        @break
                        @case('processing')
                        <span class="badge bg-primary fs-6">Processing</span>
                        @break
                        @case('completed')
                        <span class="badge bg-success fs-6">Completed</span>
                        @break
                        @case('cancelled')
                        <span class="badge bg-danger fs-6">Cancelled</span>
                        @break
                        @endswitch
                    </div>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <p class="mb-1">
                                <strong>Store:</strong> {{ $order->store->name ?? 'N/A' }}
                            </p>
                            <p class="mb-1">
                                <strong>Items:</strong> {{ $order->items->sum('quantity') }}
                            </p>
                            <p class="mb-0">
                                <strong>Payment:</strong> {{ ucfirst($order->payment_method) }}
                                @if($order->payment_status == 'paid')
                                <span class="badge bg-success">Paid</span>
                                @else
                                <span class="badge bg-warning">{{ ucfirst($order->payment_status) }}</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-3 text-md-center my-3 my-md-0">
                            <div class="text-muted small">Total</div>
                            <div class="fs-4 fw-bold">₹{{ number_format($order->total_amount, 2) }}</div>
                        </div>
                        <div class="col-md-3 text-md-end">
                            <a href="{{ route('orders.show', $order->order_number) }}" class="btn btn-outline-primary">
                                View Details
                            </a>
                        </div>
                    </div>

                    <!-- Order Items Preview -->
                    <div class="mt-3 pt-3 border-top">
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($order->items->take(3) as $item)
                            <span class="badge bg-light text-dark">
                                {{ $item->product_name }} × {{ $item->quantity }}
                            </span>
                            @endforeach
                            @if($order->items->count() > 3)
                            <span class="badge bg-secondary">+{{ $order->items->count() - 3 }} more</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @if($orders->hasPages())
    <div class="mt-4">
        {{ $orders->links() }}
    </div>
    @endif
    @else
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-bag fs-1 text-muted mb-3 d-block"></i>
            <h4>No orders yet</h4>
            <p class="text-muted mb-4">Start shopping to see your orders here</p>
            <a href="{{ route('home') }}" class="btn btn-primary">
                <i class="bi bi-shop me-1"></i>Browse Stores
            </a>
        </div>
    </div>
    @endif
</div>
@endsection