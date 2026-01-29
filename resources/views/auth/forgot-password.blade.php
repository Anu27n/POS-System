@extends('layouts.app')

@section('title', 'Forgot Password')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm mt-5">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <i class="bi bi-key text-primary" style="font-size: 3rem;"></i>
                        <h4 class="mt-2">Forgot Password?</h4>
                        <p class="text-muted">Enter your email address and we'll send you a link to reset your password.</p>
                    </div>

                    @if(session('status'))
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle me-2"></i>{{ session('status') }}
                    </div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                id="email" name="email" value="{{ old('email') }}" required autofocus
                                placeholder="Enter your email address">
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-envelope me-1"></i>Send Reset Link
                        </button>
                    </form>

                    <div class="text-center mt-4">
                        <p class="text-muted mb-0">
                            Remember your password? <a href="{{ route('login') }}">Back to Login</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
