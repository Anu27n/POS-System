@extends('layouts.admin')

@section('title', 'Payment Settings')
@section('page-title', 'Payment Gateway Settings')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <!-- Razorpay -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="bi bi-credit-card me-2"></i>Razorpay
                </h6>
                @if($gateways['razorpay']->is_active ?? false)
                    <span class="badge bg-success">Active</span>
                @endif
            </div>
            <div class="card-body">
                <form action="{{ route('admin.settings.payment.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="gateway" value="razorpay">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Key ID</label>
                            <input type="text" class="form-control" name="credentials[key_id]"
                                   value="{{ $gateways['razorpay']->credentials['key_id'] ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Key Secret</label>
                            <input type="password" class="form-control" name="credentials[key_secret]"
                                   value="{{ $gateways['razorpay']->credentials['key_secret'] ?? '' }}">
                        </div>
                    </div>

                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="razorpay_active" 
                                   name="is_active" value="1"
                                   {{ ($gateways['razorpay']->is_active ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="razorpay_active">Enable Razorpay</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="razorpay_test" 
                                   name="is_test_mode" value="1"
                                   {{ ($gateways['razorpay']->is_test_mode ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="razorpay_test">Test Mode</label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary mt-3">Save Razorpay Settings</button>
                </form>
            </div>
        </div>

        <!-- Stripe -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="bi bi-stripe me-2"></i>Stripe
                </h6>
                @if($gateways['stripe']->is_active ?? false)
                    <span class="badge bg-success">Active</span>
                @endif
            </div>
            <div class="card-body">
                <form action="{{ route('admin.settings.payment.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="gateway" value="stripe">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Publishable Key</label>
                            <input type="text" class="form-control" name="credentials[publishable_key]"
                                   value="{{ $gateways['stripe']->credentials['publishable_key'] ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Secret Key</label>
                            <input type="password" class="form-control" name="credentials[secret_key]"
                                   value="{{ $gateways['stripe']->credentials['secret_key'] ?? '' }}">
                        </div>
                    </div>

                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="stripe_active" 
                                   name="is_active" value="1"
                                   {{ ($gateways['stripe']->is_active ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="stripe_active">Enable Stripe</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="stripe_test" 
                                   name="is_test_mode" value="1"
                                   {{ ($gateways['stripe']->is_test_mode ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="stripe_test">Test Mode</label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary mt-3">Save Stripe Settings</button>
                </form>
            </div>
        </div>

        <!-- PayPal -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="bi bi-paypal me-2"></i>PayPal
                </h6>
                @if($gateways['paypal']->is_active ?? false)
                    <span class="badge bg-success">Active</span>
                @endif
            </div>
            <div class="card-body">
                <form action="{{ route('admin.settings.payment.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="gateway" value="paypal">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Client ID</label>
                            <input type="text" class="form-control" name="credentials[client_id]"
                                   value="{{ $gateways['paypal']->credentials['client_id'] ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Client Secret</label>
                            <input type="password" class="form-control" name="credentials[client_secret]"
                                   value="{{ $gateways['paypal']->credentials['client_secret'] ?? '' }}">
                        </div>
                    </div>

                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="paypal_active" 
                                   name="is_active" value="1"
                                   {{ ($gateways['paypal']->is_active ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="paypal_active">Enable PayPal</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="paypal_test" 
                                   name="is_test_mode" value="1"
                                   {{ ($gateways['paypal']->is_test_mode ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="paypal_test">Sandbox Mode</label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary mt-3">Save PayPal Settings</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Info</h6>
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Note:</strong> Only one payment gateway can be active at a time. 
                    Enabling a gateway will automatically disable others.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
