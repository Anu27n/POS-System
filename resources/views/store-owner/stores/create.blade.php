@extends('layouts.app')

@section('title', 'Create Your Store')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-shop me-2"></i>Create Your Store</h5>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted mb-4">
                        Let's set up your store! Fill in the details below to get started.
                    </p>

                    <form action="{{ route('store-owner.stores.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Store Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                    id="name" name="name" value="{{ old('name') }}" required 
                                    placeholder="My Awesome Store">
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">Store Type <span class="text-danger">*</span></label>
                                <select class="form-select @error('type') is-invalid @enderror" 
                                    id="type" name="type" required>
                                    <option value="">Select type...</option>
                                    <option value="grocery" {{ old('type') == 'grocery' ? 'selected' : '' }}>Grocery & Kirana</option>
                                    <option value="restaurant" {{ old('type') == 'restaurant' ? 'selected' : '' }}>Restaurant & Cafe</option>
                                    <option value="retail" {{ old('type') == 'retail' ? 'selected' : '' }}>Retail Store</option>
                                    <option value="clothing" {{ old('type') == 'clothing' ? 'selected' : '' }}>Clothing & Fashion</option>
                                    <option value="department" {{ old('type') == 'department' ? 'selected' : '' }}>Department Store</option>
                                    <option value="other" {{ old('type') == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                id="description" name="description" rows="3" 
                                placeholder="Tell customers about your store...">{{ old('description') }}</textarea>
                            @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                    id="phone" name="phone" value="{{ old('phone') }}" 
                                    placeholder="+91 9876543210">
                                @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Store Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                    id="email" name="email" value="{{ old('email', auth()->user()->email) }}" 
                                    placeholder="store@example.com">
                                @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Store Address</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                id="address" name="address" rows="2" 
                                placeholder="123 Main Street, City, State, Pin Code">{{ old('address') }}</textarea>
                            @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="currency" class="form-label">Currency <span class="text-danger">*</span></label>
                                <select class="form-select @error('currency') is-invalid @enderror" 
                                    id="currency" name="currency" required>
                                    <option value="INR" {{ old('currency', 'INR') == 'INR' ? 'selected' : '' }}>₹ Indian Rupee (INR)</option>
                                    <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>$ US Dollar (USD)</option>
                                    <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>€ Euro (EUR)</option>
                                    <option value="GBP" {{ old('currency') == 'GBP' ? 'selected' : '' }}>£ British Pound (GBP)</option>
                                </select>
                                @error('currency')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Back to Home
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i> Create Store
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
