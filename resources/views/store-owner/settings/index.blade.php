@extends('layouts.store-owner')

@section('title', 'Store Settings')
@section('page-title', 'Store Settings')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <form action="{{ route('store-owner.settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <!-- Subscription & Plan -->
            <div class="card mb-4 border-primary">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 text-white"><i class="bi bi-star-fill me-2"></i>Subscription & Plan</h6>
                    @if($subscription && $subscription->onTrial())
                        <span class="badge bg-warning text-dark">Trial Ends {{ $subscription->trial_ends_at->diffForHumans() }}</span>
                    @elseif($subscription && $subscription->isActive())
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-secondary">Inactive</span>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-1">{{ $subscription ? $subscription->plan->name : 'No Active Plan' }}</h4>
                            @if($subscription)
                                <p class="text-muted mb-0">
                                    Valid until: 
                                    <strong>
                                        @if($subscription->ends_at)
                                            {{ $subscription->ends_at->format('M d, Y') }}
                                        @elseif($subscription->plan)
                                            @php
                                                $baseDate = $subscription->starts_at ?? $subscription->created_at;
                                                $cycle = $subscription->plan->billing_cycle;
                                            @endphp
                                            @if($baseDate)
                                                @if($cycle == 'daily')
                                                    {{ $baseDate->copy()->addDay()->format('M d, Y') }}
                                                @elseif($cycle == 'weekly')
                                                    {{ $baseDate->copy()->addWeek()->format('M d, Y') }}
                                                @elseif($cycle == 'monthly')
                                                    {{ $baseDate->copy()->addMonth()->format('M d, Y') }}
                                                @elseif($cycle == 'quarterly')
                                                    {{ $baseDate->copy()->addMonths(3)->format('M d, Y') }}
                                                @elseif($cycle == 'yearly')
                                                    {{ $baseDate->copy()->addYear()->format('M d, Y') }}
                                                @else
                                                    Lifetime
                                                @endif
                                            @else
                                                N/A
                                            @endif
                                        @else
                                            N/A
                                        @endif
                                    </strong>
                                    @if($subscription->onTrial())
                                        <br><small class="text-warning">You are currently on a free trial.</small>
                                    @endif
                                </p>
                            @else
                                <p class="text-muted mb-0">Please subscribe to a plan to unlock all features.</p>
                            @endif
                        </div>
                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                            <div class="d-grid gap-2 d-md-block">
                                @if($subscription)
                                <a href="{{ route('pricing.checkout', $subscription->plan) }}" class="btn btn-primary">
                                    Renew Plan
                                </a>
                                @endif
                                <a href="{{ route('pricing') }}" class="btn btn-outline-primary">
                                    {{ $subscription ? 'Upgrade / Change Plan' : 'Subscribe Now' }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Basic Info -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Basic Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Store Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               name="name" value="{{ old('name', $store->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Store Type <span class="text-danger">*</span></label>
                        <select class="form-select @error('type') is-invalid @enderror" name="type" required>
                            <option value="grocery" {{ old('type', $store->type) == 'grocery' ? 'selected' : '' }}>Grocery</option>
                            <option value="clothing" {{ old('type', $store->type) == 'clothing' ? 'selected' : '' }}>Clothing</option>
                            <option value="department" {{ old('type', $store->type) == 'department' ? 'selected' : '' }}>Department Store</option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  name="description" rows="3">{{ old('description', $store->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <!-- Contact Info -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Contact Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       name="email" value="{{ old('email', $store->email) }}">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                       name="phone" value="{{ old('phone', $store->phone) }}">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea class="form-control @error('address') is-invalid @enderror" 
                                  name="address" rows="2">{{ old('address', $store->address) }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <!-- Currency Settings -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Currency Settings</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Currency</label>
                        <select class="form-select @error('currency') is-invalid @enderror" name="currency">
                            <option value="USD" {{ old('currency', $store->currency ?? 'USD') == 'USD' ? 'selected' : '' }}>USD ($)</option>
                            <option value="EUR" {{ old('currency', $store->currency) == 'EUR' ? 'selected' : '' }}>EUR (€)</option>
                            <option value="GBP" {{ old('currency', $store->currency) == 'GBP' ? 'selected' : '' }}>GBP (£)</option>
                            <option value="INR" {{ old('currency', $store->currency) == 'INR' ? 'selected' : '' }}>INR (₹)</option>
                        </select>
                        @error('currency')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        To configure tax settings, please visit the <a href="{{ route('store-owner.tax-settings.index') }}" class="alert-link">Tax Settings</a> page.
                    </div>
                </div>
            </div>
            
            <!-- Store Logo -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Store Logo</h6>
                </div>
                <div class="card-body">
                    @if($store->logo)
                        <div class="mb-3">
                            <img src="{{ asset('storage/' . $store->logo) }}" 
                                 alt="Store Logo" class="img-thumbnail" style="max-width: 200px;">
                        </div>
                    @endif
                    <div class="mb-3">
                        <input type="file" class="form-control @error('logo') is-invalid @enderror" 
                               name="logo" accept="image/*">
                        @error('logo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Leave empty to keep current logo. Max 2MB.</small>
                    </div>
                </div>
            </div>
            
            <!-- Store Status -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Store Status</h6>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-2">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" class="form-check-input" id="isActive" 
                               name="is_active" value="1" {{ old('is_active', $store->status === 'active') ? 'checked' : '' }}>
                        <label class="form-check-label" for="isActive">Store is active and accepting orders</label>
                    </div>
                    <small class="text-muted">When inactive, customers cannot place new orders from your store.</small>
                </div>
            </div>
            
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('store-owner.dashboard') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Settings</button>
            </div>
        </form>
    </div>
</div>
@endsection
