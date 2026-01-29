@extends('installer.layout')

@section('title', 'License Verification')
@section('subtitle', 'Step 2: Verify Purchase Code')
@section('show-steps', true)
@section('step1-class', 'completed')
@section('line1-class', 'completed')
@section('step2-class', 'active')

@section('content')
<h5 class="mb-4">License Verification</h5>

<div class="card mb-4">
    <div class="card-body">
        <div class="text-center mb-3">
            <i class="bi bi-key-fill text-primary" style="font-size: 3rem;"></i>
        </div>
        <p class="text-muted text-center">
            Please enter your purchase code to verify your license and continue with the installation.
        </p>
    </div>
</div>

<form action="{{ route('installer.license.store') }}" method="POST" id="licenseForm">
    @csrf
    
    <div class="mb-4">
        <label class="form-label">Purchase Code / License Key <span class="text-danger">*</span></label>
        <input type="text" 
               class="form-control form-control-lg @error('purchase_code') is-invalid @enderror" 
               name="purchase_code" 
               value="{{ old('purchase_code') }}" 
               placeholder="XXXX-XXXX-XXXX-XXXX-XXXX"
               required>
        @error('purchase_code')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-muted">Enter the purchase code you received after purchasing the product.</small>
    </div>
    
    <div class="alert alert-info">
        <i class="bi bi-info-circle me-2"></i>
        Your purchase code will be verified with our license server. An active internet connection is required.
    </div>
    
    <div class="d-flex justify-content-between mt-4">
        <a href="{{ route('installer.requirements') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back
        </a>
        <button type="submit" class="btn btn-primary" id="verifyLicense">
            Verify License <i class="bi bi-arrow-right ms-2"></i>
        </button>
    </div>
</form>

<script>
document.getElementById('licenseForm').addEventListener('submit', function() {
    const btn = document.getElementById('verifyLicense');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Verifying...';
});
</script>
@endsection
