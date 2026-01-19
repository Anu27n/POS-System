@extends('layouts.store-owner')

@section('title', 'Orders')
@section('page-title', 'Orders')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <form class="d-flex gap-2">
        <input type="search" class="form-control" name="search" 
               placeholder="Search order #..." value="{{ request('search') }}">
        <select class="form-select" name="status" style="width: auto;">
            <option value="">All Status</option>
            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
            <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
        </select>
        <button type="submit" class="btn btn-outline-primary">Filter</button>
    </form>
    <a href="{{ route('store-owner.pos.index') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>New Order (POS)
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr>
                        <td>
                            <span class="fw-semibold">#{{ $order->order_number }}</span>
                        </td>
                        <td>{{ $order->user->name ?? 'Guest' }}</td>
                        <td>
                            {{ $order->created_at->format('M d, Y') }}
                            <br>
                            <small class="text-muted">{{ $order->created_at->format('H:i') }}</small>
                        </td>
                        <td>{{ $order->items->count() }} items</td>
                        <td class="fw-semibold">${{ number_format($order->total_amount, 2) }}</td>
                        <td>
                            @if($order->payment_status == 'paid')
                                <span class="badge bg-success">Paid</span>
                            @elseif($order->payment_status == 'pending')
                                <span class="badge bg-warning">Pending</span>
                            @else
                                <span class="badge bg-danger">{{ ucfirst($order->payment_status) }}</span>
                            @endif
                            <br>
                            <small class="text-muted">{{ ucfirst($order->payment_method) }}</small>
                        </td>
                        <td>
                            @switch($order->status)
                                @case('pending')
                                    <span class="badge bg-warning">Pending</span>
                                    @break
                                @case('confirmed')
                                    <span class="badge bg-info">Confirmed</span>
                                    @break
                                @case('processing')
                                    <span class="badge bg-primary">Processing</span>
                                    @break
                                @case('completed')
                                    <span class="badge bg-success">Completed</span>
                                    @break
                                @case('cancelled')
                                    <span class="badge bg-danger">Cancelled</span>
                                    @break
                            @endswitch
                        </td>
                        <td>
                            <a href="{{ route('store-owner.orders.show', $order) }}" 
                               class="btn btn-sm btn-outline-primary" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('store-owner.orders.receipt', $order) }}" 
                               class="btn btn-sm btn-outline-secondary" title="Receipt" target="_blank">
                                <i class="bi bi-receipt"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <div class="text-muted">
                                <i class="bi bi-bag fs-1 d-block mb-2"></i>
                                No orders yet
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($orders->hasPages())
    <div class="card-footer">
        {{ $orders->appends(request()->query())->links() }}
    </div>
    @endif
</div>
@endsection
