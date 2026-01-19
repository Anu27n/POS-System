@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">Checkout</h1>
    
    <form action="{{ route('checkout.process') }}" method="POST" id="checkoutForm">
        @csrf
        
        <div class="row">
            <!-- Checkout Form -->
            <div class="col-lg-8">
                <!-- Contact Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Contact Information</h5>
                    </div>
                    <div class="card-body">
                        @auth
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-person-check me-2"></i>
                                Logged in as <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->email }})
                            </div>
                        @else
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                               name="name" value="{{ old('name') }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                               name="email" value="{{ old('email') }}" required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="mb-0">
                                <label class="form-label">Phone</label>
                                <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                       name="phone" value="{{ old('phone') }}">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <hr>
                            <p class="text-muted mb-0">
                                Already have an account? <a href="{{ route('login') }}">Login</a>
                            </p>
                        @endauth
                    </div>
                </div>
                
                <!-- Payment Method -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Payment Method</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-check payment-option">
                                    <input class="form-check-input" type="radio" name="payment_method" 
                                           id="payCounter" value="counter" checked>
                                    <label class="form-check-label w-100" for="payCounter">
                                        <div class="card">
                                            <div class="card-body text-center py-3">
                                                <i class="bi bi-cash-stack fs-1 text-success d-block mb-2"></i>
                                                <span class="fw-semibold">Pay at Counter</span>
                                                <p class="text-muted small mb-0">Pay when you pick up your order</p>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-check payment-option">
                                    <input class="form-check-input" type="radio" name="payment_method" 
                                           id="payOnline" value="online">
                                    <label class="form-check-label w-100" for="payOnline">
                                        <div class="card">
                                            <div class="card-body text-center py-3">
                                                <i class="bi bi-credit-card-2-front fs-1 text-primary d-block mb-2"></i>
                                                <span class="fw-semibold">Pay Online</span>
                                                <p class="text-muted small mb-0">Secure online payment</p>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                        @error('payment_method')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <!-- Order Notes -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Additional Notes</h5>
                    </div>
                    <div class="card-body">
                        <textarea class="form-control" name="notes" rows="3" 
                                  placeholder="Any special instructions for your order...">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>
            
            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="card sticky-top" style="top: 20px;">
                    <div class="card-header">
                        <h5 class="mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            @foreach($cart->items as $item)
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <span class="fw-semibold">{{ $item->product->name ?? 'Product' }}</span>
                                        <br>
                                        <small class="text-muted">Qty: {{ $item->quantity }} × ${{ number_format($item->product->price, 2) }}</small>
                                    </div>
                                    <span>${{ number_format($item->product->price * $item->quantity, 2) }}</span>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="card-footer">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal</span>
                            <span>${{ number_format($subtotal, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax</span>
                            <span>${{ number_format($tax, 2) }}</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <strong class="fs-5">Total</strong>
                            <strong class="fs-5">${{ number_format($total, 2) }}</strong>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-lock me-1"></i>Place Order
                            </button>
                        </div>
                        
                        <div class="text-center mt-3">
                            <small class="text-muted">
                                <i class="bi bi-shield-check me-1"></i>Secure checkout
                            </small>
                        </div>
                    </div>
                </div>
                
                <a href="{{ route('cart.index') }}" class="btn btn-outline-secondary w-100 mt-3">
                    <i class="bi bi-arrow-left me-1"></i>Back to Cart
                </a>
            </div>
        </div>
    </form>
</div>

<style>
.payment-option .form-check-input {
    display: none;
}
.payment-option .card {
    cursor: pointer;
    transition: all 0.2s;
    border: 2px solid #dee2e6;
}
.payment-option .form-check-input:checked + .form-check-label .card {
    border-color: #0d6efd;
    background-color: #f0f7ff;
}
.payment-option .card:hover {
    border-color: #0d6efd;
}
</style>
@endsection
