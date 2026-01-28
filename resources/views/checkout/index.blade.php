@extends('layouts.app')

@section('title', 'Checkout')

@push('styles')
<style>
    .payment-option .form-check-input {
        display: none;
    }

    .payment-option .card {
        cursor: pointer;
        transition: all 0.2s;
        border: 2px solid #dee2e6;
    }

    .payment-option .form-check-input:checked+.form-check-label .card {
        border-color: var(--primary-color);
        background-color: #f0f4ff;
    }

    .payment-option .card:hover {
        border-color: var(--primary-color);
    }

    .login-required-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1050;
        display: none;
    }

    .login-required-overlay.show {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* Mobile responsive styles */
    @media (max-width: 767px) {
        .payment-option .card-body {
            padding: 0.75rem;
        }
        .payment-option .card-body i {
            font-size: 1.5rem !important;
            margin-bottom: 0.25rem !important;
        }
        .payment-option .card-body .fw-semibold {
            font-size: 0.85rem;
        }
        .payment-option .card-body p {
            font-size: 0.7rem;
            margin-bottom: 0;
        }
        .col-md-6 .payment-option {
            margin-bottom: 0.5rem;
        }
        /* Order summary sticky fix */
        .sticky-top {
            position: relative !important;
            top: 0 !important;
        }
        /* Card spacing */
        .card.mb-4 {
            margin-bottom: 1rem !important;
        }
        /* Button sizing */
        .btn-lg {
            font-size: 0.95rem;
            padding: 0.6rem 1rem;
        }
    }
    
    @media (max-width: 575px) {
        .payment-option .card-body {
            padding: 0.5rem;
        }
        .payment-option .card-body i {
            font-size: 1.25rem !important;
        }
        .payment-option .card-body .fw-semibold {
            font-size: 0.8rem;
        }
        /* Contact info on small screens */
        .card-body .d-flex.align-items-center > div:first-child {
            width: 40px !important;
            height: 40px !important;
        }
        .card-body .d-flex.align-items-center > div:first-child i {
            font-size: 1.25rem !important;
        }
    }
</style>
@endpush

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
                        <div class="d-flex align-items-center">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                                style="width: 50px; height: 50px;">
                                <i class="bi bi-person-check fs-4"></i>
                            </div>
                            <div>
                                <div class="fw-semibold">{{ auth()->user()->name }}</div>
                                <div class="text-muted">{{ auth()->user()->email }}</div>
                                @if(auth()->user()->phone)
                                <div class="text-muted small">{{ auth()->user()->phone }}</div>
                                @endif
                            </div>
                        </div>
                        @else
                        <div class="alert alert-warning mb-0">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-exclamation-triangle fs-4 me-3"></i>
                                <div>
                                    <strong>Login Required</strong>
                                    <p class="mb-2">You need to login or register to complete your order.</p>
                                    <button type="button" class="btn btn-primary btn-sm me-2" data-bs-toggle="modal" data-bs-target="#loginModal">
                                        <i class="bi bi-box-arrow-in-right me-1"></i> Login
                                    </button>
                                    <a href="{{ route('register') }}" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-person-plus me-1"></i> Register
                                    </a>
                                </div>
                            </div>
                        </div>
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
                            @foreach($paymentMethods as $method => $label)
                            <div class="col-md-6">
                                <div class="form-check payment-option">
                                    <input class="form-check-input" type="radio" name="payment_method"
                                        id="pay{{ ucfirst($method) }}" value="{{ $method }}"
                                        {{ $loop->first ? 'checked' : '' }}>
                                    <label class="form-check-label w-100" for="pay{{ ucfirst($method) }}">
                                        <div class="card">
                                            <div class="card-body text-center py-3">
                                                @if($method === 'counter')
                                                <i class="bi bi-qr-code fs-1 d-block mb-2" style="color: var(--primary-color);"></i>
                                                <span class="fw-semibold">Pay at Counter</span>
                                                <p class="text-muted small mb-0">Show QR code at counter to pay</p>
                                                @elseif($method === 'razorpay')
                                                <i class="bi bi-credit-card-2-front fs-1 text-info d-block mb-2"></i>
                                                <span class="fw-semibold">Pay with Razorpay</span>
                                                <p class="text-muted small mb-0">Cards, UPI, Net Banking</p>
                                                @elseif($method === 'stripe')
                                                <i class="bi bi-stripe fs-1 text-primary d-block mb-2"></i>
                                                <span class="fw-semibold">Pay with Stripe</span>
                                                <p class="text-muted small mb-0">Cards, Apple Pay, Google Pay</p>
                                                @endif
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @if(empty($paymentMethods))
                        <div class="alert alert-warning mb-0">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            No payment methods available. Please contact the store.
                        </div>
                        @endif
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
                                        <small class="text-muted">Qty: {{ $item->quantity }} × ₹{{ number_format($item->product->price, 2) }}</small>
                                    </div>
                                    <span>₹{{ number_format($item->product->price * $item->quantity, 2) }}</span>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="card-footer">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal</span>
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
                            @auth
                            <button type="submit" class="btn btn-primary btn-lg" {{ empty($paymentMethods) ? 'disabled' : '' }}>
                                <i class="bi bi-lock me-1"></i>Place Order
                            </button>
                            @else
                            <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#loginModal">
                                <i class="bi bi-box-arrow-in-right me-1"></i>Login to Order
                            </button>
                            @endauth
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

<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="loginModalLabel">Login to Continue</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="loginForm" method="POST" action="{{ route('login') }}">
                    @csrf
                    <input type="hidden" name="redirect_to" value="{{ url()->current() }}">

                    <div class="mb-3">
                        <label class="form-label">Email or Phone <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="email" id="loginEmail" required
                            placeholder="Enter your email or phone">
                        <div class="invalid-feedback" id="emailError"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="password" id="loginPassword" required>
                        <div class="invalid-feedback" id="passwordError"></div>
                    </div>

                    <div class="alert alert-danger d-none" id="loginError"></div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg" id="loginBtn">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Login
                        </button>
                    </div>
                </form>

                <hr>

                <div class="text-center">
                    <p class="mb-2">Don't have an account?</p>
                    <a href="{{ route('register') }}?redirect={{ urlencode(url()->current()) }}" class="btn btn-outline-primary">
                        <i class="bi bi-person-plus me-1"></i> Create Account
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const btn = document.getElementById('loginBtn');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Logging in...';

        // Clear previous errors
        document.getElementById('loginError').classList.add('d-none');
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

        fetch(this.action, {
                method: 'POST',
                body: new FormData(this),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success || data.redirect) {
                    window.location.reload();
                } else if (data.errors) {
                    if (data.errors.email) {
                        document.getElementById('loginEmail').classList.add('is-invalid');
                        document.getElementById('emailError').textContent = data.errors.email[0];
                    }
                    if (data.errors.password) {
                        document.getElementById('loginPassword').classList.add('is-invalid');
                        document.getElementById('passwordError').textContent = data.errors.password[0];
                    }
                } else if (data.message) {
                    document.getElementById('loginError').textContent = data.message;
                    document.getElementById('loginError').classList.remove('d-none');
                }
            })
            .catch(error => {
                // If login was successful but returned HTML, reload the page
                window.location.reload();
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
    });
</script>
@endpush
@endsection