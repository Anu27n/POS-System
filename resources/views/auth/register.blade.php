@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm mt-5">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        @if(isset($registerAs) && $registerAs === 'store_owner')
                        <i class="bi bi-shop text-primary" style="font-size: 3rem;"></i>
                        <h4 class="mt-2">Create Store Owner Account</h4>
                        <p class="text-muted">Register to start your online store</p>
                        @else
                        <i class="bi bi-person-plus text-primary" style="font-size: 3rem;"></i>
                        <h4 class="mt-2">Create Account</h4>
                        <p class="text-muted">Join us to start ordering</p>
                        @endif
                    </div>

                    <form method="POST" action="{{ route('register') }}">
                        @csrf
                        
                        @if(isset($registerAs))
                        <input type="hidden" name="register_as" value="{{ $registerAs }}">
                        @endif
                        @if(isset($plan))
                        <input type="hidden" name="plan" value="{{ $plan }}">
                        @endif

                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                id="name" name="name" value="{{ old('name') }}" required autofocus>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                id="phone" name="phone" value="{{ old('phone') }}" required
                                placeholder="e.g., 9876543210">
                            @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address 
                                @if(isset($registerAs) && $registerAs === 'store_owner')
                                <span class="text-danger">*</span>
                                @else
                                <span class="text-muted">(Optional)</span>
                                @endif
                            </label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                id="email" name="email" value="{{ old('email') }}"
                                placeholder="e.g., john@example.com"
                                {{ isset($registerAs) && $registerAs === 'store_owner' ? 'required' : '' }}>
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                id="password" name="password" required>
                            @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control"
                                id="password_confirmation" name="password_confirmation" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            @if(isset($registerAs) && $registerAs === 'store_owner')
                            Create Store Owner Account
                            @else
                            Create Account
                            @endif
                        </button>
                    </form>

                    <div class="text-center mt-4">
                        <p class="text-muted mb-0">
                            Already have an account? 
                            <a href="{{ route('login', ['redirect_to' => 'pricing', 'plan' => $plan ?? null, 'register_as' => $registerAs ?? null]) }}">Sign In</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection