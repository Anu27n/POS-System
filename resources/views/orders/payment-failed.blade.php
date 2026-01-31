@extends('layouts.app')

@section('title', 'Payment Failed')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <!-- Failure Message -->
            <div class="text-center mb-4">
                <div class="bg-danger text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-4"
                    style="width: 100px; height: 100px;">
                    <i class="bi bi-x-lg" style="font-size: 3rem;"></i>
                </div>
                <h1 class="mb-3">Payment Failed</h1>
                <p class="lead text-muted">{{ $message ?? 'Your payment could not be processed. Please try again.' }}</p>
            </div>

            <!-- Order Details (if available) -->
            @if(isset($order))
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Order Number:</span>
                        <strong>{{ $order->order_number }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Store:</span>
                        <strong>{{ $order->store->name ?? 'Store' }}</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <strong>Total Amount:</strong>
                        <strong class="text-primary">₹{{ number_format($order->total, 2) }}</strong>
                    </div>
                </div>
            </div>
            @endif

            <!-- What to do next -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>What you can do:</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3">
                            <i class="bi bi-arrow-repeat text-primary me-2"></i>
                            <strong>Try again</strong> - Sometimes payments fail due to temporary issues
                        </li>
                        <li class="mb-3">
                            <i class="bi bi-credit-card text-primary me-2"></i>
                            <strong>Use a different payment method</strong> - Try another card or UPI
                        </li>
                        <li class="mb-3">
                            <i class="bi bi-bank text-primary me-2"></i>
                            <strong>Check with your bank</strong> - Your bank may have blocked the transaction
                        </li>
                        @if(isset($order))
                        <li>
                            <i class="bi bi-shop text-primary me-2"></i>
                            <strong>Pay at counter</strong> - You can complete payment at the store
                        </li>
                        @endif
                    </ul>
                </div>
            </div>

            <!-- Actions -->
            <div class="d-grid gap-2">
                @if(isset($order) && $order->payment_status !== 'paid')
                <a href="{{ route('payment.razorpay', $order->order_number) }}" class="btn btn-primary btn-lg">
                    <i class="bi bi-arrow-repeat me-2"></i>Try Payment Again
                </a>
                <a href="{{ route('order.confirmation', $order) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-receipt me-2"></i>View Order Details
                </a>
                @endif
                <a href="{{ route('home') }}" class="btn btn-outline-primary">
                    <i class="bi bi-house me-2"></i>Go to Homepage
                </a>
            </div>

            <!-- Support Contact -->
            <div class="text-center mt-4">
                <p class="text-muted small mb-0">
                    <i class="bi bi-headset me-1"></i>
                    Need help? Contact the store for assistance.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
