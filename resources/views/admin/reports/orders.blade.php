@extends('layouts.admin')

@section('title', 'Orders Report')
@section('page-title', 'Orders Report')

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label">Start Date</label>
                <input type="date" class="form-control" name="start_date" value="{{ $startDate }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">End Date</label>
                <input type="date" class="form-control" name="end_date" value="{{ $endDate }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Store</label>
                <select class="form-select" name="store_id">
                    <option value="">All Stores</option>
                    @foreach($stores as $store)
                        <option value="{{ $store->id }}" {{ $storeId == $store->id ? 'selected' : '' }}>
                            {{ $store->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                    <option value="">All Status</option>
                    <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="confirmed" {{ $status == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="processing" {{ $status == 'processing' ? 'selected' : '' }}>Processing</option>
                    <option value="completed" {{ $status == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ $status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Payment Status</label>
                <select class="form-select" name="payment_status">
                    <option value="">All</option>
                    <option value="pending" {{ $paymentStatus == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="paid" {{ $paymentStatus == 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="failed" {{ $paymentStatus == 'failed' ? 'selected' : '' }}>Failed</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Generate Report</button>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="stat-label">Total Orders</div>
                <div class="stat-value">{{ $orders->total() }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card" style="border-left-color: #059669;">
            <div class="card-body">
                <div class="stat-label">Completed</div>
                <div class="stat-value">{{ $completedCount }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card" style="border-left-color: #d97706;">
            <div class="card-body">
                <div class="stat-label">Pending</div>
                <div class="stat-value">{{ $pendingCount }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card" style="border-left-color: #dc2626;">
            <div class="card-body">
                <div class="stat-label">Cancelled</div>
                <div class="stat-value">{{ $cancelledCount }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Orders Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Orders</h6>
        <a href="{{ route('admin.reports.orders', array_merge(request()->query(), ['export' => 'csv'])) }}" 
           class="btn btn-outline-primary btn-sm">
            <i class="bi bi-download me-1"></i>Export CSV
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Store</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr>
                        <td>
                            <span class="fw-semibold">{{ $order->order_number }}</span>
                        </td>
                        <td>{{ $order->store->name ?? 'N/A' }}</td>
                        <td>{{ $order->user->name ?? 'Guest' }}</td>
                        <td>{{ $order->created_at->format('M d, Y H:i') }}</td>
                        <td>{{ $order->items->count() }}</td>
                        <td>${{ number_format($order->total_amount, 2) }}</td>
                        <td>
                            @if($order->payment_status == 'paid')
                                <span class="badge bg-success">Paid</span>
                            @elseif($order->payment_status == 'pending')
                                <span class="badge bg-warning">Pending</span>
                            @else
                                <span class="badge bg-danger">{{ ucfirst($order->payment_status) }}</span>
                            @endif
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
                                @default
                                    <span class="badge bg-secondary">{{ ucfirst($order->status) }}</span>
                            @endswitch
                        </td>
                        <td>
                            <a href="#" class="btn btn-sm btn-outline-primary" 
                               data-bs-toggle="modal" data-bs-target="#orderModal{{ $order->id }}">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">No orders found</td>
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

<!-- Order Detail Modals -->
@foreach($orders as $order)
<div class="modal fade" id="orderModal{{ $order->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Order Details #{{ $order->order_number }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <p><strong>Store:</strong> {{ $order->store->name ?? 'N/A' }}</p>
                        <p><strong>Customer:</strong> {{ $order->user->name ?? 'Guest' }}</p>
                        <p><strong>Date:</strong> {{ $order->created_at->format('M d, Y H:i') }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Payment Method:</strong> {{ ucfirst($order->payment_method) }}</p>
                        <p><strong>Payment Status:</strong> {{ ucfirst($order->payment_status) }}</p>
                        <p><strong>Order Status:</strong> {{ ucfirst($order->status) }}</p>
                    </div>
                </div>
                
                <h6>Order Items</h6>
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                        <tr>
                            <td>{{ $item->product_name }}</td>
                            <td>${{ number_format($item->price, 2) }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>${{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-end">Subtotal:</th>
                            <th>${{ number_format($order->subtotal, 2) }}</th>
                        </tr>
                        <tr>
                            <th colspan="3" class="text-end">Tax:</th>
                            <th>${{ number_format($order->tax_amount, 2) }}</th>
                        </tr>
                        <tr>
                            <th colspan="3" class="text-end">Total:</th>
                            <th>${{ number_format($order->total_amount, 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
                
                @if($order->notes)
                <p><strong>Notes:</strong> {{ $order->notes }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endforeach
@endsection
