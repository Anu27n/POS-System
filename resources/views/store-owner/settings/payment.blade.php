@extends('layouts.store-owner')

@section('title', 'Payment Settings')
@section('page-title', 'Payment Settings')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <form action="{{ route('store-owner.payment-settings.update') }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Payment Methods -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Payment Methods</h6>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="enable_counter_payment" 
                               id="enableCounter" value="1" {{ old('enable_counter_payment', $store->enable_counter_payment) ? 'checked' : '' }}>
                        <label class="form-check-label" for="enableCounter">
                            <strong>Pay at Counter</strong>
                            <div class="text-muted small">Allow customers to pay at the store counter</div>
                        </label>
                    </div>

                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="enable_online_payment" 
                               id="enableOnline" value="1" {{ old('enable_online_payment', $store->enable_online_payment) ? 'checked' : '' }}>
                        <label class="form-check-label" for="enableOnline">
                            <strong>Online Payment</strong>
                            <div class="text-muted small">Allow customers to pay online via payment gateways</div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Test Mode -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_test_mode" 
                               id="testMode" value="1" {{ old('is_test_mode', $store->is_test_mode) ? 'checked' : '' }}>
                        <label class="form-check-label" for="testMode">
                            <strong>Test Mode</strong>
                            <div class="text-muted small">Enable test mode to use sandbox/test API keys for development</div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Razorpay -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-credit-card me-2"></i>Razorpay</h6>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" name="razorpay_enabled" 
                               id="razorpayEnabled" value="1" {{ old('razorpay_enabled', $store->razorpay_enabled) ? 'checked' : '' }}>
                        <label class="form-check-label" for="razorpayEnabled">Enable</label>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Key ID</label>
                                <input type="text" class="form-control @error('razorpay_key_id') is-invalid @enderror" 
                                       name="razorpay_key_id" value="{{ old('razorpay_key_id', $store->razorpay_key_id) }}"
                                       placeholder="rzp_test_xxx or rzp_live_xxx">
                                @error('razorpay_key_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Key Secret</label>
                                <input type="password" class="form-control @error('razorpay_key_secret') is-invalid @enderror" 
                                       name="razorpay_key_secret" placeholder="{{ $store->razorpay_key_secret ? '••••••••' : 'Enter secret key' }}">
                                @error('razorpay_key_secret')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Leave empty to keep existing secret</small>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Get your Razorpay API keys from <a href="https://dashboard.razorpay.com/app/keys" target="_blank">Razorpay Dashboard</a>
                    </div>
                </div>
            </div>

            <!-- Stripe -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-stripe me-2"></i>Stripe</h6>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" name="stripe_enabled" 
                               id="stripeEnabled" value="1" {{ old('stripe_enabled', $store->stripe_enabled) ? 'checked' : '' }}>
                        <label class="form-check-label" for="stripeEnabled">Enable</label>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Publishable Key</label>
                                <input type="text" class="form-control @error('stripe_publishable_key') is-invalid @enderror" 
                                       name="stripe_publishable_key" value="{{ old('stripe_publishable_key', $store->stripe_publishable_key) }}"
                                       placeholder="pk_test_xxx or pk_live_xxx">
                                @error('stripe_publishable_key')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Secret Key</label>
                                <input type="password" class="form-control @error('stripe_secret_key') is-invalid @enderror" 
                                       name="stripe_secret_key" placeholder="{{ $store->stripe_secret_key ? '••••••••' : 'Enter secret key' }}">
                                @error('stripe_secret_key')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Leave empty to keep existing secret</small>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Get your Stripe API keys from <a href="https://dashboard.stripe.com/apikeys" target="_blank">Stripe Dashboard</a>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Save Payment Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
