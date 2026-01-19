@extends('installer.layout')

@section('title', 'Installation Complete')
@section('subtitle', 'Your POS System is ready!')

@section('content')
<div class="text-center">
    <div class="mb-4">
        <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center"
             style="width: 80px; height: 80px;">
            <i class="bi bi-check-lg" style="font-size: 2.5rem;"></i>
        </div>
    </div>
    
    <h2 class="h4 mb-3">Installation Complete!</h2>
    <p class="text-muted mb-4">
        Your POS System has been successfully installed and is ready to use.
    </p>
    
    <div class="card mb-4 text-start">
        <div class="card-header">
            <strong>What's Next?</strong>
        </div>
        <div class="card-body">
            <ul class="list-unstyled mb-0">
                <li class="mb-2">
                    <i class="bi bi-1-circle text-primary me-2"></i>
                    Login with your admin account
                </li>
                <li class="mb-2">
                    <i class="bi bi-2-circle text-primary me-2"></i>
                    Create stores and add store owners
                </li>
                <li class="mb-2">
                    <i class="bi bi-3-circle text-primary me-2"></i>
                    Configure payment gateways (optional)
                </li>
                <li class="mb-0">
                    <i class="bi bi-4-circle text-primary me-2"></i>
                    Start accepting orders!
                </li>
            </ul>
        </div>
    </div>
    
    <div class="alert alert-warning text-start">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <strong>Important:</strong> For security, please delete or rename the <code>/install</code> routes 
        after installation is complete.
    </div>
    
    <div class="d-grid gap-2">
        <a href="{{ route('login') }}" class="btn btn-primary btn-lg">
            <i class="bi bi-box-arrow-in-right me-2"></i>Go to Login
        </a>
        <a href="{{ url('/') }}" class="btn btn-outline-secondary">
            <i class="bi bi-house me-2"></i>Visit Homepage
        </a>
    </div>
</div>
@endsection
