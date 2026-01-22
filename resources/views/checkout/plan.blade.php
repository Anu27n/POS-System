@extends('layouts.app')

@section('title', 'Checkout - ' . $plan->name)

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="mb-4">
                <a href="{{ route('pricing') }}" class="text-decoration-none">
                    <i class="bi bi-arrow-left me-1"></i> Back to Pricing
                </a>
            </div>

            <div class="row">
                <!-- Order Summary -->
                <div class="col-md-5 order-md-2 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="bi bi-cart me-2"></i>Order Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h5 class="mb-0">{{ $plan->name }}</h5>
                                    <small class="text-muted">{{ ucfirst($plan->billing_cycle) }} Plan</small>
                                </div>
                                <span class="badge bg-primary">{{ ucfirst($plan->billing_cycle) }}</span>
                            </div>
                            
                            <hr>

                            <div class="mb-3">
                                <strong>Plan Features:</strong>
                                <ul class="list-unstyled mt-2 mb-0">
                                    @foreach($plan->getAvailableFeatures() as $feature)
                                        <li class="py-1">
                                            <i class="bi bi-check-circle text-success me-2"></i>
                                            {{ $feature['name'] ?? $feature }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>

                            <hr>

                            @if($plan->trial_days > 0)
                                <div class="alert alert-info mb-3">
                                    <i class="bi bi-gift me-2"></i>
                                    <strong>{{ $plan->trial_days }}-day free trial included!</strong>
                                    <br><small>You won't be charged until trial ends.</small>
                                </div>
                            @endif

                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal</span>
                                <span>{{ \App\Helpers\CurrencyHelper::format($plan->price) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Tax (GST 18%)</span>
                                <span>{{ \App\Helpers\CurrencyHelper::format($plan->price * 0.18) }}</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <strong>Total</strong>
                                <strong class="text-primary fs-4">{{ \App\Helpers\CurrencyHelper::format($plan->price * 1.18) }}</strong>
                            </div>
                        </div>
                    </div>

                    @if($existingSubscription)
                        <div class="alert alert-warning mt-3">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Note:</strong> You have an active subscription to <strong>{{ $existingSubscription->plan->name }}</strong>. 
                            Subscribing to this plan will cancel your current subscription.
                        </div>
                    @endif
                </div>

                <!-- Checkout Form -->
                <div class="col-md-7 order-md-1">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-credit-card me-2"></i>Payment Method</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('pricing.subscribe', $plan) }}" method="POST">
                                @csrf
                                
                                <div class="mb-4">
                                    <label class="form-label fw-semibold">Store Information</label>
                                    <div class="bg-light p-3 rounded">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-shop fs-3 me-3 text-primary"></i>
                                            <div>
                                                <strong>{{ $store->name }}</strong>
                                                <br><small class="text-muted">{{ $store->email }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if($plan->price == 0)
                                    <div class="alert alert-success">
                                        <i class="bi bi-check-circle me-2"></i>
                                        This is a free plan. No payment required!
                                    </div>
                                    <button type="submit" class="btn btn-success btn-lg w-100">
                                        <i class="bi bi-check-lg me-2"></i>Activate Free Plan
                                    </button>
                                @elseif($plan->trial_days > 0)
                                    <div class="alert alert-info">
                                        <i class="bi bi-gift me-2"></i>
                                        Start your <strong>{{ $plan->trial_days }}-day free trial</strong>. 
                                        No payment required now!
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-lg w-100">
                                        <i class="bi bi-play-fill me-2"></i>Start Free Trial
                                    </button>
                                @else
                                    <div class="mb-4">
                                        <label class="form-label fw-semibold">Select Payment Method</label>
                                        
                                        <div class="row g-3">
                                            <div class="col-6">
                                                <div class="form-check payment-method-card">
                                                    <input class="form-check-input" type="radio" name="payment_method" id="razorpay" value="razorpay" checked>
                                                    <label class="form-check-label w-100" for="razorpay">
                                                        <div class="card h-100 border-2" style="border-color: #072654;">
                                                            <div class="card-body text-center py-3">
                                                                <img src="https://razorpay.com/assets/razorpay-glyph.svg" alt="Razorpay" height="40" class="mb-2">
                                                                <div class="fw-semibold">Razorpay</div>
                                                                <small class="text-muted">Cards, UPI, Wallets</small>
                                                            </div>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-check payment-method-card">
                                                    <input class="form-check-input" type="radio" name="payment_method" id="stripe" value="stripe">
                                                    <label class="form-check-label w-100" for="stripe">
                                                        <div class="card h-100 border-2">
                                                            <div class="card-body text-center py-3">
                                                                <img src="https://stripe.com/img/v3/home/social.png" alt="Stripe" height="40" class="mb-2">
                                                                <div class="fw-semibold">Stripe</div>
                                                                <small class="text-muted">International Cards</small>
                                                            </div>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-primary btn-lg w-100">
                                        <i class="bi bi-lock me-2"></i>Proceed to Payment
                                    </button>
                                @endif
                            </form>

                            <div class="text-center mt-4">
                                <small class="text-muted">
                                    <i class="bi bi-shield-lock me-1"></i>
                                    Secure payment powered by industry-leading encryption
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.payment-method-card input[type="radio"] {
    position: absolute;
    opacity: 0;
}
.payment-method-card input[type="radio"]:checked + label .card {
    border-color: var(--bs-primary) !important;
    box-shadow: 0 0 0 3px rgba(var(--bs-primary-rgb), 0.2);
}
</style>
@endsection
