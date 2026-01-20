@extends('layouts.app')

@section('title', 'Order #' . $order->order_number)

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('orders.index') }}">My Orders</a></li>
            <li class="breadcrumb-item active">Order #{{ $order->order_number }}</li>
        </ol>
    </nav>

    <div class="row">
        <!-- Order Details -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0">Order #{{ $order->order_number }}</h4>
                        <small class="text-muted">{{ $order->created_at->format('M d, Y H:i') }}</small>
                    </div>
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
                                    <td>{{ $item->product_name }}</td>
                                    <td>
                                        @if($item->options)
                                        @foreach($item->options as $key => $value)
                                        <span class="badge bg-light text-dark">
                                            {{ ucfirst($key) }}: {{ $value }}
                                        </span>
                                        @endforeach
                                        @else
                                        -
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
                                    <td colspan="4" class="text-end">Subtotal</td>
                                    <td class="text-end">₹{{ number_format($order->subtotal, 2) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-end">Tax</td>
                                    <td class="text-end">₹{{ number_format($order->tax_amount, 2) }}</td>
                                </tr>
                                @if($order->discount_amount > 0)
                                <tr>
                                    <td colspan="4" class="text-end">Discount</td>
                                    <td class="text-end text-success">-₹{{ number_format($order->discount_amount, 2) }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <th colspan="4" class="text-end">Total</th>
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
            <!-- Store Info -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Store Information</h6>
                </div>
                <div class="card-body">
                    <h5>{{ $order->store->name ?? 'Store' }}</h5>
                    @if($order->store)
                    @if($order->store->address)
                    <p class="mb-1">
                        <i class="bi bi-geo-alt me-1"></i>{{ $order->store->address }}
                    </p>
                    @endif
                    @if($order->store->phone)
                    <p class="mb-1">
                        <i class="bi bi-telephone me-1"></i>{{ $order->store->phone }}
                    </p>
                    @endif
                    @if($order->store->email)
                    <p class="mb-0">
                        <i class="bi bi-envelope me-1"></i>{{ $order->store->email }}
                    </p>
                    @endif
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
                        <strong>Method:</strong> {{ ucfirst($order->payment_method) }}
                    </p>
                    <p class="mb-0">
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
                    <p class="mb-0 mt-2">
                        <strong>Transaction ID:</strong><br>
                        <code>{{ $order->transaction_id }}</code>
                    </p>
                    @endif
                </div>
            </div>

            <!-- Verification QR -->
            @if($order->hasQrCode() && in_array($order->order_status, ['pending', 'confirmed', 'processing']))
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Pickup Verification</h6>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <img src="{{ $order->qr_code_data_uri }}"
                            alt="Verification QR Code"
                            style="max-width: 200px; width: 100%;"
                            class="border rounded p-2 bg-white">
                    </div>
                    <p class="mb-0 small text-muted">Show this QR code when picking up your order</p>
                </div>
            </div>
            @endif

            <!-- Actions -->
            <div class="d-grid gap-2">
                <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Back to Orders
                </a>
                @if($order->store)
                <a href="{{ route('store.show', $order->store->slug) }}" class="btn btn-primary">
                    <i class="bi bi-shop me-1"></i>Shop Again
                </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection