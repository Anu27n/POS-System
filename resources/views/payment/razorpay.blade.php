@extends('layouts.app')

@section('title', 'Payment - ' . $order->order_number)

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-credit-card me-2"></i>
                        Complete Payment
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Order Summary -->
                    <div class="mb-4">
                        <h6 class="text-muted mb-3">Order Summary</h6>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Order Number:</span>
                            <strong>{{ $order->order_number }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Store:</span>
                            <strong>{{ $store->name }}</strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span>{{ $store->currency_symbol }}{{ number_format($order->subtotal, 2) }}</span>
                        </div>
                        @if($order->tax > 0)
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax:</span>
                            <span>{{ $store->currency_symbol }}{{ number_format($order->tax, 2) }}</span>
                        </div>
                        @endif
                        @if($order->discount > 0)
                        <div class="d-flex justify-content-between mb-2">
                            <span>Discount:</span>
                            <span class="text-success">-{{ $store->currency_symbol }}{{ number_format($order->discount, 2) }}</span>
                        </div>
                        @endif
                        <hr>
                        <div class="d-flex justify-content-between">
                            <strong>Total Amount:</strong>
                            <strong class="text-primary fs-5">{{ $store->currency_symbol }}{{ number_format($order->total, 2) }}</strong>
                        </div>
                    </div>

                    <!-- Razorpay Button -->
                    <div class="text-center">
                        <button id="rzp-button" class="btn btn-primary btn-lg w-100">
                            <i class="bi bi-lock me-2"></i>
                            Pay {{ $store->currency_symbol }}{{ number_format($order->total, 2) }}
                        </button>
                        <p class="text-muted small mt-2 mb-0">
                            <i class="bi bi-shield-check me-1"></i>
                            Secured by Razorpay
                        </p>
                    </div>

                    <!-- Cancel Link -->
                    <div class="text-center mt-3">
                        <a href="{{ route('order.confirmation', $order) }}" class="text-muted">
                            Cancel and pay later at counter
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var options = {
        "key": "{{ $razorpayKeyId }}",
        "amount": "{{ $order->total * 100 }}",
        "currency": "{{ $store->currency ?? 'INR' }}",
        "name": "{{ $store->name }}",
        "description": "Order #{{ $order->order_number }}",
        "image": "{{ $store->logo_url ?? '' }}",
        "handler": function (response) {
            // Create form and submit to callback
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("payment.razorpay.callback") }}';
            
            var csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);

            var paymentId = document.createElement('input');
            paymentId.type = 'hidden';
            paymentId.name = 'razorpay_payment_id';
            paymentId.value = response.razorpay_payment_id;
            form.appendChild(paymentId);

            var orderId = document.createElement('input');
            orderId.type = 'hidden';
            orderId.name = 'razorpay_order_id';
            orderId.value = response.razorpay_order_id || '';
            form.appendChild(orderId);

            var signature = document.createElement('input');
            signature.type = 'hidden';
            signature.name = 'razorpay_signature';
            signature.value = response.razorpay_signature || '';
            form.appendChild(signature);

            document.body.appendChild(form);
            form.submit();
        },
        "prefill": {
            @if($order->user)
            "name": "{{ $order->user->name }}",
            "email": "{{ $order->user->email }}",
            @endif
        },
        "notes": {
            "order_number": "{{ $order->order_number }}"
        },
        "theme": {
            "color": "{{ $store->primary_color ?? '#3399cc' }}"
        },
        "modal": {
            "ondismiss": function() {
                // User closed the payment window
                console.log('Payment cancelled by user');
            }
        }
    };

    var rzp = new Razorpay(options);
    
    rzp.on('payment.failed', function (response){
        // Redirect to failure page
        window.location.href = '{{ route("payment.razorpay.failed") }}';
    });

    document.getElementById('rzp-button').onclick = function(e) {
        e.preventDefault();
        rzp.open();
    };
});
</script>
@endpush
