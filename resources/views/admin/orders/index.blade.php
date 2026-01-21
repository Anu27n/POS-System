@extends('layouts.admin')

@section('title', 'All Orders')
@section('page-title', 'Order Management')

@section('content')
<div class="container-fluid">
    <!-- Quick Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-white-50">Total Orders</h6>
                            <h3 class="mb-0">{{ $totalOrders ?? 0 }}</h3>
                        </div>
                        <i class="bi bi-receipt fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-dark opacity-75">Pending</h6>
                            <h3 class="mb-0">{{ $pendingOrders ?? 0 }}</h3>
                        </div>
                        <i class="bi bi-hourglass-split fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-white-50">Completed</h6>
                            <h3 class="mb-0">{{ $completedOrders ?? 0 }}</h3>
                        </div>
                        <i class="bi bi-check-circle fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <a href="{{ route('admin.orders.scanner') }}" class="card border-0 shadow-sm bg-info text-white text-decoration-none">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50">QR Scanner</h6>
                            <h5 class="mb-0">Verify Orders</h5>
                        </div>
                        <i class="bi bi-qr-code-scan fs-1 opacity-75"></i>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.orders.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small text-muted">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Order number or customer..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Store</label>
                    <select name="store" class="form-select">
                        <option value="">All Stores</option>
                        @foreach($stores ?? [] as $store)
                        <option value="{{ $store->id }}" {{ request('store') == $store->id ? 'selected' : '' }}>
                            {{ $store->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Payment</label>
                    <select name="payment" class="form-select">
                        <option value="">All Payment</option>
                        <option value="pending" {{ request('payment') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="paid" {{ request('payment') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="failed" {{ request('payment') == 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Date</label>
                    <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Order #</th>
                        <th>Store</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Date</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders ?? [] as $order)
                    <tr>
                        <td>
                            <a href="{{ route('admin.orders.show', $order) }}" class="fw-semibold text-decoration-none">
                                {{ $order->order_number }}
                            </a>
                        </td>
                        <td>
                            <small class="text-muted">{{ $order->store->name ?? 'N/A' }}</small>
                        </td>
                        <td>
                            {{ $order->user->name ?? $order->customer_name ?? 'Walk-in' }}
                            @if($order->user)
                            <br><small class="text-muted">{{ $order->user->email }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $order->items->count() }} items</span>
                        </td>
                        <td>
                            <strong>₹{{ number_format($order->total_amount, 2) }}</strong>
                        </td>
                        <td>
                            @php
                            $statusClasses = [
                            'pending' => 'bg-warning text-dark',
                            'confirmed' => 'bg-info text-white',
                            'processing' => 'bg-primary',
                            'completed' => 'bg-success',
                            'cancelled' => 'bg-danger'
                            ];
                            @endphp
                            <span class="badge {{ $statusClasses[$order->order_status] ?? 'bg-secondary' }}">
                                {{ ucfirst($order->order_status) }}
                            </span>
                        </td>
                        <td>
                            @php
                            $paymentClasses = [
                            'pending' => 'text-warning',
                            'paid' => 'text-success',
                            'failed' => 'text-danger'
                            ];
                            @endphp
                            <span class="{{ $paymentClasses[$order->payment_status] ?? '' }}">
                                <i class="bi bi-{{ $order->payment_status == 'paid' ? 'check-circle-fill' : ($order->payment_status == 'failed' ? 'x-circle-fill' : 'clock') }}"></i>
                                {{ ucfirst($order->payment_status) }}
                            </span>
                        </td>
                        <td>
                            <small>{{ $order->created_at->format('M d, Y') }}</small>
                            <br>
                            <small class="text-muted">{{ $order->created_at->format('h:i A') }}</small>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-outline-primary" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.orders.receipt', $order) }}" class="btn btn-outline-secondary" title="Download Receipt" target="_blank">
                                    <i class="bi bi-file-earmark-pdf"></i>
                                </a>
                                @if($order->order_status !== 'completed' && $order->order_status !== 'cancelled')
                                @if($order->payment_status !== 'paid')
                                <button type="button" class="btn btn-outline-warning" title="Mark as Paid"
                                    onclick="markAsPaid({{ $order->id }})">
                                    <i class="bi bi-currency-rupee"></i>
                                </button>
                                @endif
                                <button type="button" class="btn btn-outline-success" title="Complete Order"
                                    onclick="completeOrder({{ $order->id }})">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4">
                            <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-2 mb-0">No orders found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($orders) && $orders->hasPages())
        <div class="card-footer">
            {{ $orders->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>

<script>
    function markAsPaid(orderId) {
        if (!confirm('Are you sure you want to mark this order as paid?')) return;

        fetch(`/admin/orders/${orderId}/mark-paid`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error marking order as paid');
            });
    }

    function completeOrder(orderId) {
        if (!confirm('Are you sure you want to complete this order?')) return;

        fetch(`/admin/orders/${orderId}/complete`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            });
    }
</script>
@endsection