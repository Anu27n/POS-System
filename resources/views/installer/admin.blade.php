@extends('installer.layout')

@section('title', 'Admin Account')
@section('subtitle', 'Step 5: Create Admin Account')
@section('show-steps', true)
@section('step1-class', 'completed')
@section('line1-class', 'completed')
@section('step2-class', 'completed')
@section('line2-class', 'completed')
@section('step3-class', 'completed')
@section('line3-class', 'completed')
@section('step4-class', 'completed')
@section('line4-class', 'completed')
@section('step5-class', 'active')

@section('content')
<h5 class="mb-4">Create Admin Account</h5>

<form action="{{ route('installer.admin.store') }}" method="POST">
    @csrf
    
    <div class="card mb-4">
        <div class="card-header">
            <strong>Application Settings</strong>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">Application Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('app_name') is-invalid @enderror" 
                       name="app_name" value="{{ old('app_name', 'POS System') }}" required>
                @error('app_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">The name of your POS system</small>
            </div>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <strong>Administrator Account</strong>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                       name="name" value="{{ old('name') }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <label class="form-label">Email <span class="text-danger">*</span></label>
                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                       name="email" value="{{ old('email') }}" required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">This will be your login email</small>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Password <span class="text-danger">*</span></label>
                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                       name="password" required minlength="8">
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">Minimum 8 characters</small>
            </div>
            
            <div class="mb-0">
                <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                <input type="password" class="form-control" name="password_confirmation" required>
            </div>
        </div>
    </div>
    
    <div class="d-flex justify-content-between">
        <a href="{{ route('installer.migrations') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back
        </a>
        <button type="submit" class="btn btn-success" id="finishInstall">
            <i class="bi bi-check-lg me-2"></i>Complete Installation
        </button>
    </div>
</form>

<script>
document.getElementById('finishInstall').closest('form').addEventListener('submit', function() {
    const btn = document.getElementById('finishInstall');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Installing...';
});
</script>
@endsection
