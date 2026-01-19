@extends('layouts.app')

@section('title', 'Welcome')

@section('content')
<div class="container">
    <!-- Hero Section -->
    <div class="row align-items-center py-5">
        <div class="col-lg-6">
            <h1 class="display-4 fw-bold mb-4">Scan, Order, Skip the Queue</h1>
            <p class="lead text-muted mb-4">
                Experience the future of shopping with our QR code-based ordering system. 
                Browse products, add to cart, and pay - all from your phone.
            </p>
            <div class="d-flex gap-3">
                @guest
                    <a href="{{ route('register') }}" class="btn btn-primary btn-lg">Get Started</a>
                    <a href="{{ route('login') }}" class="btn btn-outline-primary btn-lg">Sign In</a>
                @else
                    <a href="{{ route('orders.index') }}" class="btn btn-primary btn-lg">My Orders</a>
                @endguest
            </div>
        </div>
        <div class="col-lg-6 text-center">
            <i class="bi bi-qr-code-scan text-primary" style="font-size: 15rem; opacity: 0.8;"></i>
        </div>
    </div>

    <!-- How It Works -->
    <div class="py-5">
        <h2 class="text-center mb-5">How It Works</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 text-center p-4">
                    <div class="mb-3">
                        <i class="bi bi-qr-code text-primary" style="font-size: 3rem;"></i>
                    </div>
                    <h5>1. Scan QR Code</h5>
                    <p class="text-muted">Scan the store's QR code with your phone to access their product catalog.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 text-center p-4">
                    <div class="mb-3">
                        <i class="bi bi-cart-check text-primary" style="font-size: 3rem;"></i>
                    </div>
                    <h5>2. Add to Cart</h5>
                    <p class="text-muted">Browse products, check prices, and add items to your cart.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 text-center p-4">
                    <div class="mb-3">
                        <i class="bi bi-credit-card text-primary" style="font-size: 3rem;"></i>
                    </div>
                    <h5>3. Pay & Collect</h5>
                    <p class="text-muted">Pay online or at the counter, then collect your items hassle-free.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Featured Stores -->
    @if($featuredStores->count() > 0)
    <div class="py-5">
        <h2 class="text-center mb-5">Featured Stores</h2>
        <div class="row g-4">
            @foreach($featuredStores as $store)
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        @if($store->logo)
                            <img src="{{ Storage::url($store->logo) }}" alt="{{ $store->name }}" 
                                 class="rounded-circle mb-3" style="width: 80px; height: 80px; object-fit: cover;">
                        @else
                            <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                                 style="width: 80px; height: 80px; font-size: 2rem;">
                                {{ strtoupper(substr($store->name, 0, 1)) }}
                            </div>
                        @endif
                        <h5 class="card-title">{{ $store->name }}</h5>
                        <p class="text-muted small mb-3">
                            <span class="badge bg-secondary">{{ ucfirst($store->type) }}</span>
                        </p>
                        <a href="{{ route('store.show', $store->slug) }}" class="btn btn-outline-primary btn-sm">
                            Visit Store
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
