@extends('layouts.admin')

@section('title', 'Edit Plan')
@section('page-title', 'Edit Plan: ' . $plan->name)

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Plan Details</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.plans.update', $plan) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label for="name" class="form-label">Plan Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $plan->name) }}" required>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="sort_order" class="form-label">Sort Order</label>
                            <input type="number" class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order" value="{{ old('sort_order', $plan->sort_order) }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $plan->description) }}</textarea>
                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="price" class="form-label">Price (₹) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price', $plan->price) }}" required>
                            @error('price')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="billing_cycle" class="form-label">Billing Cycle <span class="text-danger">*</span></label>
                            <select class="form-select @error('billing_cycle') is-invalid @enderror" id="billing_cycle" name="billing_cycle" required>
                                <option value="monthly" {{ old('billing_cycle', $plan->billing_cycle) === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                <option value="quarterly" {{ old('billing_cycle', $plan->billing_cycle) === 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                                <option value="yearly" {{ old('billing_cycle', $plan->billing_cycle) === 'yearly' ? 'selected' : '' }}>Yearly</option>
                            </select>
                            @error('billing_cycle')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="trial_days" class="form-label">Trial Days</label>
                            <input type="number" class="form-control @error('trial_days') is-invalid @enderror" id="trial_days" name="trial_days" value="{{ old('trial_days', $plan->trial_days) }}" min="0">
                            @error('trial_days')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                         <div class="col-md-6">
                            <div class="form-check pt-4">
                                <input class="form-check-input" type="checkbox" id="tax_enabled" name="tax_enabled" value="1" {{ old('tax_enabled', $plan->tax_enabled) ? 'checked' : '' }} onchange="toggleTaxInput()">
                                <label class="form-check-label" for="tax_enabled">
                                    <strong>Enable Tax</strong>
                                    <small class="text-muted d-block">Apply tax on this plan subscription</small>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6" id="tax-input-container" style="display: none;">
                            <label for="tax_percentage" class="form-label">Tax Percentage (%)</label>
                            <input type="number" step="0.01" class="form-control @error('tax_percentage') is-invalid @enderror" id="tax_percentage" name="tax_percentage" value="{{ old('tax_percentage', $plan->tax_percentage) }}" min="0" max="100">
                             @error('tax_percentage')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_popular" name="is_popular" value="1" {{ old('is_popular', $plan->is_popular) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_popular">
                                    <strong>Mark as Popular</strong>
                                    <small class="text-muted d-block">Highlight this plan on the pricing page</small>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $plan->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    <strong>Active</strong>
                                    <small class="text-muted d-block">Make this plan available for purchase</small>
                                </label>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <h5 class="mb-3">Select Features</h5>
                    <p class="text-muted mb-3">Choose which features are included in this plan</p>

                    @php $selectedFeatures = old('features', $plan->features ?? []); @endphp

                    @foreach($categories as $categorySlug => $categoryName)
                    @php $categoryFeatures = $groupedFeatures->get($categorySlug, collect()); @endphp
                    @if($categoryFeatures->count() > 0)
                    <div class="mb-4">
                        <h6 class="text-uppercase text-muted mb-2">{{ $categoryName }}</h6>
                        <div class="row">
                            @foreach($categoryFeatures as $feature)
                            <div class="col-md-6 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                        id="feature_{{ $feature->slug }}"
                                        name="features[]"
                                        value="{{ $feature->slug }}"
                                        {{ in_array($feature->slug, $selectedFeatures) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="feature_{{ $feature->slug }}">
                                        {{ $feature->name }}
                                        @if($feature->description)
                                        <small class="text-muted d-block">{{ $feature->description }}</small>
                                        @endif
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    @endforeach

                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('admin.plans.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> Update Plan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Plan Statistics</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Active Subscriptions:</span>
                    <strong>{{ $plan->subscriptions()->where('status', 'active')->count() }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Trial Subscriptions:</span>
                    <strong>{{ $plan->subscriptions()->where('status', 'trial')->count() }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Total Subscriptions:</span>
                    <strong>{{ $plan->subscriptions()->count() }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        window.toggleTaxInput = function() {
            const isEnabled = document.getElementById('tax_enabled').checked;
            document.getElementById('tax-input-container').style.display = isEnabled ? 'block' : 'none';
        }
        toggleTaxInput(); // Initial State
    });
</script>
@endpush