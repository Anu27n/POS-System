@extends('layouts.admin')

@section('title', 'Order ' . $order->order_number)
@section('page-title', 'Order Details')

@push('styles')
<style>
    .order-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 24px;
    }

    .status-badge {
        display: inline-block;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 500;
    }

    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }

    .status-confirmed {
        background: #dbeafe;
        color: #1e40af;
    }

    .status-processing {
        background: #e0e7ff;
        color: #3730a3;
    }

    .status-completed {
        background: #d1fae5;
        color: #065f46;
    }

    .status-cancelled {
        background: #fee2e2;
        color: #991b1b;
    }

    .payment-paid {
        background: #d1fae5;
        color: #065f46;
    }

    .payment-pending {
        background: #fef3c7;
        color: #92400e;
    }

    .payment-failed {
        background: #fee2e2;
        color: #991b1b;
    }

    .info-card {
        border-radius: 12px;
        border: 1px solid #e5e7eb;
    }

    .info-card .card-header {
        background: #f8fafc;
        font-weight: 600;
        border-bottom: 1px solid #e5e7eb;
    }

    .qr-code-img {
        max-width: 180px;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        padding: 8px;
        background: white;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Back Button -->
    <div class="mb-3">
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back to Orders
        </a>
    </div>

    <!-- Order Header -->
    <div class="order-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h3 class="mb-1">{{ $order->order_number }}</h3>
                <p class="mb-0 opacity-75">
                    <i class="bi bi-calendar3 me-1"></i>
                    {{ $order->created_at->format('F d, Y \a\t h:i A') }}
                </p>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <span class="status-badge status-{{ $order->order_status }} me-2">
                    {{ ucfirst($order->order_status) }}
                </span>
                <span class="status-badge payment-{{ $order->payment_status }}">
                    Payment: {{ ucfirst($order->payment_status) }}
                </span>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Store Info -->
            <div class="card info-card mb-4">
                <div class="card-header">
                    <i class="bi bi-shop me-2"></i>Store Information
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="text-muted small">Store Name</label>
                            <p class="mb-2 fw-semibold">{{ $order->store->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Store Owner</label>
                            <p class="mb-2">{{ $order->store->owner->name ?? 'N/A' }}</p>
                        </div>
                        @if($order->store)
                        <div class="col-md-6">
                            <label class="text-muted small">Location</label>
                            <p class="mb-0">{{ $order->store->address ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Contact</label>
                            <p class="mb-0">{{ $order->store->phone ?? 'N/A' }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Customer Info -->
            <div class="card info-card mb-4">
                <div class="card-header">
                    <i class="bi bi-person me-2"></i>Customer Information
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="text-muted small">Customer Name</label>
                            <p class="mb-2 fw-semibold">{{ $order->user->name ?? $order->customer_name ?? 'Walk-in Customer' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Email</label>
                            <p class="mb-2">{{ $order->user->email ?? $order->customer_email ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Phone</label>
                            <p class="mb-0">{{ $order->customer_phone ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Customer Type</label>
                            <p class="mb-0">
                                @if($order->user_id)
                                <span class="badge bg-primary">Registered User</span>
                                @else
                                <span class="badge bg-secondary">Walk-in</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="card info-card">
                <div class="card-header">
                    <i class="bi bi-bag me-2"></i>Order Items
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($item->product && $item->product->image)
                                        <img src="{{ asset('storage/' . $item->product->image) }}"
                                            alt="{{ $item->product_name }}"
                                            class="rounded me-3"
                                            style="width: 50px; height: 50px; object-fit: cover;">
                                        @else
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center me-3"
                                            style="width: 50px; height: 50px;">
                                            <i class="bi bi-image text-muted"></i>
                                        </div>
                                        @endif
                                        <div>
                                            <strong>{{ $item->product_name }}</strong>
                                            @if($item->notes)
                                            <br><small class="text-muted">{{ $item->notes }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary">{{ $item->quantity }}</span>
                                </td>
                                <td class="text-end">₹{{ number_format($item->unit_price, 2) }}</td>
                                <td class="text-end fw-semibold">₹{{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="3" class="text-end">Subtotal</td>
                                <td class="text-end">₹{{ number_format($order->subtotal, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end">Tax</td>
                                <td class="text-end">₹{{ number_format($order->tax_amount, 2) }}</td>
                            </tr>
                            @if($order->discount_amount > 0)
                            <tr>
                                <td colspan="3" class="text-end">Discount</td>
                                <td class="text-end text-success">-₹{{ number_format($order->discount_amount, 2) }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td colspan="3" class="text-end"><strong class="fs-5">Total</strong></td>
                                <td class="text-end"><strong class="fs-5">₹{{ number_format($order->total_amount, 2) }}</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- QR Code -->
            @if($order->hasQrCode())
            <div class="card info-card mb-4">
                <div class="card-header">
                    <i class="bi bi-qr-code me-2"></i>Verification QR Code
                </div>
                <div class="card-body text-center">
                    <img src="{{ $order->qr_code_url }}" alt="Order QR Code" class="qr-code-img mb-3">
                    <p class="text-muted small mb-0">Scan this code to verify the order</p>
                </div>
            </div>
            @endif

            <!-- Payment Info -->
            <div class="card info-card mb-4">
                <div class="card-header">
                    <i class="bi bi-credit-card me-2"></i>Payment Information
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Method</span>
                        <span class="fw-semibold">{{ strtoupper($order->payment_method) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Status</span>
                        <span class="status-badge payment-{{ $order->payment_status }} py-1 px-2">
                            {{ ucfirst($order->payment_status) }}
                        </span>
                    </div>
                    @if($order->paid_at)
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Paid At</span>
                        <span>{{ $order->paid_at->format('M d, Y h:i A') }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div class="card info-card">
                <div class="card-header">
                    <i class="bi bi-lightning me-2"></i>Actions
                </div>
                <div class="card-body d-grid gap-2">
                    @if($order->payment_status !== 'paid' && $order->order_status !== 'cancelled')
                    <form action="{{ route('admin.orders.mark-paid', $order) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-cash me-2"></i>Mark as Paid
                        </button>
                    </form>
                    @endif

                    @if($order->order_status !== 'completed' && $order->order_status !== 'cancelled')
                    <form action="{{ route('admin.orders.update-status', $order) }}" method="POST" class="d-flex gap-2">
                        @csrf
                        <select name="status" class="form-select">
                            <option value="pending" {{ $order->order_status == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="confirmed" {{ $order->order_status == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="processing" {{ $order->order_status == 'processing' ? 'selected' : '' }}>Processing</option>
                            <option value="completed" {{ $order->order_status == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ $order->order_status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </form>

                    <form action="{{ route('admin.orders.complete', $order) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary w-100"
                            onclick="return confirm('Complete this order? It will no longer be scannable.')">
                            <i class="bi bi-check-circle me-2"></i>Complete Order
                        </button>
                    </form>
                    @endif

                    <a href="{{ route('admin.orders.receipt', $order) }}" class="btn btn-outline-secondary" target="_blank">
                        <i class="bi bi-file-earmark-pdf me-2"></i>Download Receipt
                    </a>

                    <a href="{{ route('admin.orders.scanner') }}" class="btn btn-outline-primary">
                        <i class="bi bi-qr-code-scan me-2"></i>Scan Another Order
                    </a>
                </div>
            </div>

            <!-- Notes -->
            @if($order->notes)
            <div class="card info-card mt-4">
                <div class="card-header">
                    <i class="bi bi-sticky me-2"></i>Order Notes
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $order->notes }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection