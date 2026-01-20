@extends('layouts.store-owner')

@section('title', 'Order #' . $order->order_number)
@section('page-title', 'Order Details')

@section('content')
<div class="row">
    <!-- Order Info -->
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Order #{{ $order->order_number }}</h5>
                    <small class="text-muted">{{ $order->created_at->format('M d, Y H:i') }}</small>
                </div>
                <div>
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
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Options</th>
                                <th class="text-end">Price</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $item->product_name }}</div>
                                    @if($item->product)
                                    <small class="text-muted">SKU: {{ $item->product->sku ?? 'N/A' }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($item->options)
                                    @foreach($item->options as $key => $value)
                                    <span class="badge bg-light text-dark">{{ ucfirst($key) }}: {{ $value }}</span>
                                    @endforeach
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end">₹{{ number_format($item->price, 2) }}</td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-end">₹{{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-end">Subtotal:</td>
                                <td class="text-end">₹{{ number_format($order->subtotal, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end">Tax:</td>
                                <td class="text-end">₹{{ number_format($order->tax_amount, 2) }}</td>
                            </tr>
                            @if($order->discount_amount > 0)
                            <tr>
                                <td colspan="4" class="text-end">Discount:</td>
                                <td class="text-end text-success">-₹{{ number_format($order->discount_amount, 2) }}</td>
                            </tr>
                            @endif
                            <tr>
                                <th colspan="4" class="text-end">Total:</th>
                                <th class="text-end fs-5">₹{{ number_format($order->total_amount, 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        @if($order->notes)
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Order Notes</h6>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $order->notes }}</p>
            </div>
        </div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Customer Info -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Customer Information</h6>
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>Name:</strong> {{ $order->user->name ?? 'Guest' }}</p>
                @if($order->user)
                <p class="mb-2"><strong>Email:</strong> {{ $order->user->email }}</p>
                <p class="mb-2"><strong>Phone:</strong> {{ $order->user->phone ?? 'N/A' }}</p>
                @endif
            </div>
        </div>

        <!-- Payment Info -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Payment Information</h6>
            </div>
            <div class="card-body">
                <p class="mb-2">
                    <strong>Method:</strong>
                    {{ ucfirst($order->payment_method) }}
                </p>
                <p class="mb-2">
                    <strong>Status:</strong>
                    @if($order->payment_status == 'paid')
                    <span class="badge bg-success">Paid</span>
                    @elseif($order->payment_status == 'pending')
                    <span class="badge bg-warning">Pending</span>
                    @else
                    <span class="badge bg-danger">{{ ucfirst($order->payment_status) }}</span>
                    @endif
                </p>
                @if($order->transaction_id)
                <p class="mb-0">
                    <strong>Transaction ID:</strong>
                    <code>{{ $order->transaction_id }}</code>
                </p>
                @endif
            </div>
        </div>

        <!-- Update Status -->
        @if($order->status !== 'completed' && $order->status !== 'cancelled')
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Update Status</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('store-owner.orders.update-status', $order) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="mb-3">
                        <select class="form-select" name="order_status" required>
                            <option value="pending" {{ $order->order_status == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="confirmed" {{ $order->order_status == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="processing" {{ $order->order_status == 'processing' ? 'selected' : '' }}>Processing</option>
                            <option value="completed" {{ $order->order_status == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ $order->order_status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Update Status</button>
                </form>
            </div>
        </div>
        @endif

        <!-- Actions -->
        <div class="d-grid gap-2">
            <a href="{{ route('store-owner.orders.receipt', $order) }}"
                class="btn btn-outline-primary" target="_blank">
                <i class="bi bi-receipt me-1"></i>Print Receipt
            </a>
            <a href="{{ route('store-owner.orders.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back to Orders
            </a>
        </div>
    </div>
</div>
@endsection