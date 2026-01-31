@extends('layouts.app')

@section('title', 'Complete Payment')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white text-center py-4">
                    <h4 class="mb-0"><i class="bi bi-shield-lock me-2"></i>Complete Your Payment</h4>
                </div>
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="bi bi-credit-card fs-1 text-primary"></i>
                        </div>
                        <h5>{{ $plan->name }} Plan</h5>
                        <p class="text-muted mb-0">{{ ucfirst($plan->billing_cycle) }} Subscription</p>
                    </div>

                    <div class="bg-light p-3 rounded mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Plan Price</span>
                            <span>{{ \App\Helpers\CurrencyHelper::format($plan->price) }}</span>
                        </div>
                        @if($plan->tax_enabled && $plan->tax_percentage > 0)
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax (GST {{ $plan->tax_percentage + 0 }}%)</span>
                            <span>{{ \App\Helpers\CurrencyHelper::format(($plan->price * $plan->tax_percentage) / 100) }}</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <strong>Total Amount</strong>
                            <strong class="text-primary fs-5" id="amount">{{ \App\Helpers\CurrencyHelper::format($plan->price + (($plan->price * $plan->tax_percentage) / 100)) }}</strong>
                        </div>
                        @else
                        <hr>
                        <div class="d-flex justify-content-between">
                            <strong>Total Amount</strong>
                            <strong class="text-primary fs-5" id="amount">{{ \App\Helpers\CurrencyHelper::format($plan->price) }}</strong>
                        </div>
                        @endif
                    </div>

                    @if($method === 'razorpay')
                    <!-- Razorpay Payment -->
                    <div id="razorpay-section">
                        <button id="razorpay-btn" class="btn btn-primary btn-lg w-100">
                            <i class="bi bi-credit-card me-2"></i>Pay with Razorpay
                        </button>
                        <form id="razorpay-form" action="{{ route('pricing.razorpay-callback', $plan) }}" method="POST" style="display: none;">
                            @csrf
                            <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
                            <input type="hidden" name="razorpay_order_id" id="razorpay_order_id">
                            <input type="hidden" name="razorpay_signature" id="razorpay_signature">
                        </form>
                    </div>
                    @else
                    <!-- Stripe Payment -->
                    <div id="stripe-section">
                        <form id="stripe-form" action="{{ route('pricing.stripe-callback', $plan) }}" method="POST">
                            @csrf
                            <input type="hidden" name="stripe_token" id="stripe_token">

                            <div class="mb-3">
                                <label class="form-label">Card Number</label>
                                <div id="card-element" class="form-control" style="padding: 12px;"></div>
                                <div id="card-errors" class="text-danger mt-2" role="alert"></div>
                            </div>

                            <button type="submit" id="stripe-btn" class="btn btn-primary btn-lg w-100">
                                <i class="bi bi-lock me-2"></i>Pay {{ \App\Helpers\CurrencyHelper::format($plan->tax_enabled ? ($plan->price + ($plan->price * $plan->tax_percentage / 100)) : $plan->price) }}
                            </button>
                        </form>
                    </div>
                    @endif

                    <div class="text-center mt-4">
                        <a href="{{ route('pricing.checkout', $plan) }}" class="text-muted">
                            <i class="bi bi-arrow-left me-1"></i> Back to Checkout
                        </a>
                    </div>
                </div>
                <div class="card-footer text-center bg-light py-3">
                    <small class="text-muted">
                        <i class="bi bi-shield-check me-1"></i>
                        256-bit SSL Encrypted • PCI DSS Compliant
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
@if($method === 'razorpay')
@php
    $razorpaySettings = \App\Models\PaymentSetting::getGateway('razorpay');
    $razorpayKey = $razorpaySettings && isset($razorpaySettings->credentials['key_id']) 
        ? $razorpaySettings->credentials['key_id'] 
        : config('services.razorpay.key', '');
@endphp
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
    document.getElementById('razorpay-btn').onclick = function(e) {
        e.preventDefault();

        var razorpayKey = "{{ $razorpayKey }}";
        
        if (!razorpayKey || razorpayKey === '') {
            alert('Razorpay is not configured. Please contact the administrator.');
            return;
        }

        var options = {
            "key": razorpayKey,
            "amount": {{ ($plan->tax_enabled ? ($plan->price + ($plan->price * $plan->tax_percentage / 100)) : $plan->price) * 100 }}, // Amount in paise
            "currency": "INR",
            "name": "{{ config('app.name') }}",
            "description": "{{ $plan->name }} Plan - {{ ucfirst($plan->billing_cycle) }}",
            "image": "/images/logo.png",
            "handler": function(response) {
                document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
                document.getElementById('razorpay_order_id').value = response.razorpay_order_id || 'manual';
                document.getElementById('razorpay_signature').value = response.razorpay_signature || 'manual';
                document.getElementById('razorpay-form').submit();
            },
            "prefill": {
                "name": "{{ auth()->user()->name }}",
                "email": "{{ auth()->user()->email }}",
                "contact": "{{ $store->phone ?? '' }}"
            },
            "theme": {
                "color": "#030a22"
            },
            "modal": {
                "ondismiss": function() {
                    console.log('Payment cancelled');
                }
            }
        };

        var rzp = new Razorpay(options);
        rzp.open();
    }
</script>
@else
@php
    $stripeSettings = \App\Models\PaymentSetting::getGateway('stripe');
    $stripeKey = $stripeSettings && isset($stripeSettings->credentials['publishable_key']) 
        ? $stripeSettings->credentials['publishable_key'] 
        : config('services.stripe.key', '');
@endphp
<script src="https://js.stripe.com/v3/"></script>
<script>
    var stripeKey = "{{ $stripeKey }}";
    
    if (!stripeKey || stripeKey === '') {
        alert('Stripe is not configured. Please contact the administrator.');
    }
    
    var stripe = Stripe(stripeKey);
    var elements = stripe.elements();

    var style = {
        base: {
            fontSize: '16px',
            color: '#32325d',
            '::placeholder': {
                color: '#aab7c4'
            }
        },
        invalid: {
            color: '#dc3545',
            iconColor: '#dc3545'
        }
    };

    var cardElement = elements.create('card', {
        style: style
    });
    cardElement.mount('#card-element');

    cardElement.on('change', function(event) {
        var displayError = document.getElementById('card-errors');
        if (event.error) {
            displayError.textContent = event.error.message;
        } else {
            displayError.textContent = '';
        }
    });

    var form = document.getElementById('stripe-form');
    form.addEventListener('submit', async function(event) {
        event.preventDefault();

        var btn = document.getElementById('stripe-btn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';

        const {
            token,
            error
        } = await stripe.createToken(cardElement);

        if (error) {
            var displayError = document.getElementById('card-errors');
            displayError.textContent = error.message;
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-lock me-2"></i>Pay {{ \App\Helpers\CurrencyHelper::format($plan->tax_enabled ? ($plan->price + ($plan->price * $plan->tax_percentage / 100)) : $plan->price) }}';
        } else {
            document.getElementById('stripe_token').value = token.id;
            form.submit();
        }
    });
</script>
@endif
@endpush
@endsection