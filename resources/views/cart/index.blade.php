@extends('layouts.app')

@section('title', 'Shopping Cart')

@push('styles')
<style>
    /* Mobile responsive cart styles */
    @media (max-width: 767px) {
        /* Cart item layout */
        .d-flex.align-items-center.p-3 {
            flex-wrap: wrap;
            padding: 0.75rem !important;
        }
        .d-flex.align-items-center.p-3 > .me-3 {
            margin-right: 0.75rem !important;
        }
        .d-flex.align-items-center.p-3 > .me-3 img,
        .d-flex.align-items-center.p-3 > .me-3 > div {
            width: 60px !important;
            height: 60px !important;
        }
        .d-flex.align-items-center.p-3 > .flex-grow-1 h6 {
            font-size: 0.9rem;
        }
        .d-flex.align-items-center.p-3 > .flex-grow-1 div {
            font-size: 0.85rem;
        }
        /* Quantity controls and subtotal on mobile */
        .d-flex.align-items-center.p-3 > .d-flex.align-items-center.gap-2 {
            margin-top: 0.5rem;
            order: 3;
        }
        .d-flex.align-items-center.p-3 > .text-end.ms-4 {
            margin-left: auto !important;
            margin-top: 0;
            min-width: auto !important;
        }
        /* Order summary sticky */
        .col-lg-4 .card {
            position: relative !important;
        }
        /* Cart header */
        .card-header h5 {
            font-size: 1rem;
        }
        /* Buttons */
        .btn-outline-danger.btn-sm {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        .btn-outline-secondary.btn-sm {
            padding: 0.25rem 0.5rem;
        }
        /* Total section */
        .fs-5 {
            font-size: 1rem !important;
        }
    }
    
    @media (max-width: 575px) {
        .d-flex.align-items-center.p-3 > .me-3 img,
        .d-flex.align-items-center.p-3 > .me-3 > div {
            width: 50px !important;
            height: 50px !important;
        }
        .d-flex.align-items-center.p-3 > .flex-grow-1 h6 {
            font-size: 0.85rem;
        }
        .btn-primary.btn-lg {
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
        }
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <h1 class="mb-4">Shopping Cart</h1>

    @if($cart && $cart->items->count() > 0)
    <div class="row">
        <!-- Cart Items -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $cart->store->name ?? 'Store' }}</h5>
                    <form action="{{ route('cart.clear') }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger"
                            onclick="return confirm('Clear all items from cart?')">
                            <i class="bi bi-trash me-1"></i>Clear Cart
                        </button>
                    </form>
                </div>
                <div class="card-body p-0">
                    @foreach($cart->items as $item)
                    <div class="d-flex align-items-center p-3 border-bottom">
                        <!-- Product Image -->
                        <div class="me-3">
                            @if($item->product && $item->product->image)
                            <img src="{{ asset('storage/' . $item->product->image) }}"
                                alt="{{ $item->product->name }}"
                                class="rounded" style="width: 80px; height: 80px; object-fit: cover;">
                            @else
                            <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                style="width: 80px; height: 80px;">
                                <i class="bi bi-box text-muted fs-3"></i>
                            </div>
                            @endif
                        </div>

                        <!-- Product Details -->
                        <div class="flex-grow-1">
                            <h6 class="mb-1">{{ $item->product->name ?? 'Product' }}</h6>
                            <div class="text-primary fw-semibold">₹{{ number_format($item->price, 2) }}</div>
                        </div>

                        <!-- Quantity Controls -->
                        <div class="d-flex align-items-center gap-2">
                            <form action="{{ route('cart.update', $item) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="quantity" value="{{ $item->quantity - 1 }}">
                                <button type="submit" class="btn btn-outline-secondary btn-sm"
                                    {{ $item->quantity <= 1 ? 'disabled' : '' }}>-</button>
                            </form>

                            <span class="fw-semibold px-2">{{ $item->quantity }}</span>

                            <form action="{{ route('cart.update', $item) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="quantity" value="{{ $item->quantity + 1 }}">
                                <button type="submit" class="btn btn-outline-secondary btn-sm">+</button>
                            </form>
                        </div>

                        <!-- Subtotal & Remove -->
                        <div class="text-end ms-4" style="min-width: 100px;">
                            <div class="fw-bold">₹{{ number_format($item->subtotal, 2) }}</div>
                            <form action="{{ route('cart.remove', $item) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-link btn-sm text-danger p-0">
                                    Remove
                                </button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-3">
                <a href="{{ route('store.show', $cart->store->slug ?? '') }}" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-left me-1"></i>Continue Shopping
                </a>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal ({{ $cart->items->sum('quantity') }} items)</span>
                        <span>₹{{ number_format($subtotal, 2) }}</span>
                    </div>
                    @if(!empty($taxBreakdown))
                        @foreach($taxBreakdown as $taxItem)
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">{{ $taxItem['name'] }} ({{ number_format($taxItem['percentage'], 2) }}%)</span>
                            <span class="text-muted small">₹{{ number_format($taxItem['amount'], 2) }}</span>
                        </div>
                        @endforeach
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Tax</span>
                            <span>₹{{ number_format($tax, 2) }}</span>
                        </div>
                    @else
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax</span>
                            <span>₹{{ number_format($tax, 2) }}</span>
                        </div>
                    @endif
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <strong class="fs-5">Total</strong>
                        <strong class="fs-5">₹{{ number_format($total, 2) }}</strong>
                    </div>

                    <div class="d-grid">
                        <a href="{{ route('checkout.index') }}" class="btn btn-primary btn-lg">
                            Proceed to Checkout
                        </a>
                    </div>
                </div>
            </div>

            <!-- Store Info -->
            @if($cart->store)
            <div class="card mt-3">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        @if($cart->store->logo)
                        <img src="{{ asset('storage/' . $cart->store->logo) }}"
                            alt="{{ $cart->store->name }}"
                            class="rounded-circle me-3"
                            style="width: 50px; height: 50px; object-fit: cover;">
                        @else
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                            style="width: 50px; height: 50px;">
                            <i class="bi bi-shop"></i>
                        </div>
                        @endif
                        <div>
                            <h6 class="mb-0">{{ $cart->store->name }}</h6>
                            <small class="text-muted">{{ ucfirst($cart->store->type) }} Store</small>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
    @else
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-cart-x fs-1 text-muted mb-3 d-block"></i>
            <h4>Your cart is empty</h4>
            <p class="text-muted mb-4">Start shopping by browsing our stores</p>
            <a href="{{ route('home') }}#stores" class="btn btn-primary">
                <i class="bi bi-shop me-1"></i>Browse Stores
            </a>
        </div>
    </div>
    @endif
</div>
@endsection