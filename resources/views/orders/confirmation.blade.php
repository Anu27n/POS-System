@php
use Illuminate\Support\Facades\Storage;
@endphp

@extends('layouts.app')

@section('title', 'Order Confirmed')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            @if($order->payment_method === 'counter')
            <!-- Pay at Counter - QR Code Display -->
            <div class="card border-0 shadow-lg mb-4" style="background: linear-gradient(135deg, #030a22 0%, #1a2744 100%);">
                <div class="card-body text-center text-white py-5">
                    <div class="mb-4">
                        <i class="bi bi-qr-code-scan" style="font-size: 3rem;"></i>
                    </div>
                    <h2 class="mb-3">Please Show This QR at the Counter</h2>
                    <p class="mb-4 opacity-75">Present this QR code to the cashier to complete your payment</p>

                    <!-- QR Code -->
                    <div class="bg-white p-4 rounded-3 d-inline-block mb-4">
                        @php
                        $qrService = app(\App\Services\QRCodeService::class);
                        $qrImage = $qrService->generateOrderQR($order);
                        @endphp
                        <img src="{{ $qrImage }}" alt="Order QR Code" style="width: 250px; height: 250px;">
                    </div>

                    <div class="mb-3">
                        <h3 class="mb-1">Order #{{ $order->order_number }}</h3>
                        <p class="h4 mb-0">Total: ₹{{ number_format($order->total, 2) }}</p>
                    </div>

                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <span class="badge bg-warning text-dark fs-6 px-3 py-2">
                            <i class="bi bi-clock me-1"></i>Awaiting Payment
                        </span>
                        <span class="badge bg-light text-dark fs-6 px-3 py-2">
                            <i class="bi bi-shop me-1"></i>{{ $order->store->name }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Payment Instructions -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="bi bi-info-circle text-primary me-2"></i>How to Complete Your Payment
                    </h5>
                    <div class="row g-4">
                        <div class="col-md-4 text-center">
                            <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                                <span class="fw-bold">1</span>
                            </div>
                            <h6>Go to Counter</h6>
                            <p class="text-muted small mb-0">Head to the payment counter at the store</p>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                                <span class="fw-bold">2</span>
                            </div>
                            <h6>Show QR Code</h6>
                            <p class="text-muted small mb-0">Present this QR code to the cashier</p>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                                <span class="fw-bold">3</span>
                            </div>
                            <h6>Complete Payment</h6>
                            <p class="text-muted small mb-0">Pay via Cash, Card, or UPI</p>
                        </div>
                    </div>
                </div>
            </div>
            @else
            <!-- Success Message for Online/Other Payments -->
            <div class="text-center mb-5">
                <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-4"
                    style="width: 100px; height: 100px;">
                    <i class="bi bi-check-lg" style="font-size: 3rem;"></i>
                </div>
                <h1 class="mb-3">Order Confirmed!</h1>
                <p class="lead text-muted">Thank you for your order. Your order has been received.</p>
            </div>
            @endif

            <!-- Order Details Card -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Order #{{ $order->order_number }}</h5>
                        <small class="text-muted">{{ $order->created_at->format('M d, Y H:i') }}</small>
                    </div>
                    <span class="badge bg-{{ $order->status == 'pending' ? 'warning' : 'success' }} fs-6">
                        {{ ucfirst($order->status) }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Store</h6>
                            <p class="mb-0">
                                <strong>{{ $order->store->name ?? 'Store' }}</strong><br>
                                @if($order->store->address)
                                {{ $order->store->address }}<br>
                                @endif
                                @if($order->store->phone)
                                <i class="bi bi-telephone me-1"></i>{{ $order->store->phone }}
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Payment</h6>
                            <p class="mb-0">
                                <strong>{{ ucfirst($order->payment_method) }}</strong><br>
                                Status:
                                @if($order->payment_status == 'paid')
                                <span class="text-success">Paid</span>
                                @else
                                <span class="text-warning">{{ ucfirst($order->payment_status) }}</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <h6 class="text-muted mb-3">Order Items</h6>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                <tr>
                                    <td>
                                        {{ $item->product_name }}
                                        @if($item->options)
                                        <br>
                                        <small class="text-muted">
                                            @foreach($item->options as $key => $value)
                                            {{ ucfirst($key) }}: {{ $value }}@if(!$loop->last), @endif
                                            @endforeach
                                        </small>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-end">₹{{ number_format($item->price, 2) }}</td>
                                    <td class="text-end">₹{{ number_format($item->subtotal, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end">Subtotal</td>
                                    <td class="text-end">₹{{ number_format($order->subtotal, 2) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end">Tax</td>
                                    <td class="text-end">₹{{ number_format($order->tax, 2) }}</td>
                                </tr>
                                @if($order->discount > 0)
                                <tr>
                                    <td colspan="3" class="text-end">Discount</td>
                                    <td class="text-end text-success">-₹{{ number_format($order->discount, 2) }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <th colspan="3" class="text-end">Total</th>
                                    <th class="text-end fs-5">₹{{ number_format($order->total, 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    @if($order->notes)
                    <div class="mt-3">
                        <h6 class="text-muted mb-2">Order Notes</h6>
                        <p class="mb-0">{{ $order->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- QR Code for Verification (for non-counter payments) -->
            @if($order->payment_method !== 'counter')
            <div class="card mb-4">
                <div class="card-body text-center">
                    <h5 class="mb-3">Order Verification QR Code</h5>
                    <div class="mb-3">
                        @php
                        $qrService = app(\App\Services\QRCodeService::class);
                        $qrImage = $qrService->generateOrderQR($order);
                        @endphp
                        <img src="{{ $qrImage }}" alt="Order QR Code" class="img-fluid" style="max-width: 200px;">
                    </div>
                    <p class="mb-2 text-muted">Show this QR code when picking up your order</p>
                    <p class="mb-0 small text-muted">Order verification is secure and store-specific</p>
                </div>
            </div>
            @endif

            <!-- Actions -->
            <div class="d-flex justify-content-center gap-3">
                @auth
                <a href="{{ route('orders.index') }}" class="btn btn-outline-primary">
                    <i class="bi bi-list-ul me-1"></i>View All Orders
                </a>
                @endauth
                <a href="{{ route('store.show', $order->store->slug ?? '') }}" class="btn btn-primary">
                    <i class="bi bi-shop me-1"></i>Continue Shopping
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Auto-refresh order status -->
@if($order->payment_status !== 'paid' || !in_array($order->order_status, ['completed', 'cancelled']))
<script>
    // Poll for order status updates every 5 seconds
    let statusCheckInterval;
    let currentPaymentStatus = '{{ $order->payment_status }}';
    let currentOrderStatus = '{{ $order->order_status }}';
    
    function checkOrderStatus() {
        fetch('{{ route("order.confirmation", $order->order_number) }}', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.payment_status !== currentPaymentStatus || data.order_status !== currentOrderStatus) {
                // Status changed - reload page
                location.reload();
            }
        })
        .catch(err => console.log('Status check failed:', err));
    }
    
    // Start polling after 3 seconds
    setTimeout(() => {
        statusCheckInterval = setInterval(checkOrderStatus, 5000);
    }, 3000);
    
    // Stop polling after 10 minutes
    setTimeout(() => {
        if (statusCheckInterval) clearInterval(statusCheckInterval);
    }, 600000);
    
    // Clean up on page hide
    document.addEventListener('visibilitychange', () => {
        if (document.hidden && statusCheckInterval) {
            clearInterval(statusCheckInterval);
        } else if (!document.hidden) {
            statusCheckInterval = setInterval(checkOrderStatus, 5000);
        }
    });
</script>
@endif
@endsection