@extends('layouts.admin')

@section('title', 'Create Plan')
@section('page-title', 'Create New Plan')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Plan Details</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.plans.store') }}" method="POST">
                    @csrf

                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label for="name" class="form-label">Plan Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="sort_order" class="form-label">Sort Order</label>
                            <input type="number" class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="price" class="form-label">Price (₹) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price', 0) }}" required>
                            @error('price')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="billing_cycle" class="form-label">Billing Cycle <span class="text-danger">*</span></label>
                            <select class="form-select @error('billing_cycle') is-invalid @enderror" id="billing_cycle" name="billing_cycle" required>
                                <option value="monthly" {{ old('billing_cycle') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                <option value="quarterly" {{ old('billing_cycle') === 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                                <option value="yearly" {{ old('billing_cycle') === 'yearly' ? 'selected' : '' }}>Yearly</option>
                            </select>
                            @error('billing_cycle')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="trial_days" class="form-label">Trial Days</label>
                            <input type="number" class="form-control @error('trial_days') is-invalid @enderror" id="trial_days" name="trial_days" value="{{ old('trial_days', 0) }}" min="0">
                            @error('trial_days')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                         <div class="col-md-6">
                            <div class="form-check pt-4">
                                <input class="form-check-input" type="checkbox" id="tax_enabled" name="tax_enabled" value="1" {{ old('tax_enabled') ? 'checked' : '' }} onchange="toggleTaxInput()">
                                <label class="form-check-label" for="tax_enabled">
                                    <strong>Enable Tax</strong>
                                    <small class="text-muted d-block">Apply tax on this plan subscription</small>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6" id="tax-input-container" style="display: none;">
                            <label for="tax_percentage" class="form-label">Tax Percentage (%)</label>
                            <input type="number" step="0.01" class="form-control @error('tax_percentage') is-invalid @enderror" id="tax_percentage" name="tax_percentage" value="{{ old('tax_percentage', 0) }}" min="0" max="100">
                             @error('tax_percentage')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_popular" name="is_popular" value="1" {{ old('is_popular') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_popular">
                                    <strong>Mark as Popular</strong>
                                    <small class="text-muted d-block">Highlight this plan on the pricing page</small>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
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
                                        {{ in_array($feature->slug, old('features', [])) ? 'checked' : '' }}>
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

                    @if($features->isEmpty())
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        No features defined yet. <a href="{{ route('admin.plan-features.index') }}">Create features first</a> or
                        <a href="{{ route('admin.plan-features.seed-defaults') }}" onclick="event.preventDefault(); document.getElementById('seed-form').submit();">seed default features</a>.
                    </div>
                    <form id="seed-form" action="{{ route('admin.plan-features.seed-defaults') }}" method="POST" class="d-none">@csrf</form>
                    @endif

                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('admin.plans.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> Create Plan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Preview</h5>
            </div>
            <div class="card-body text-center">
                <div class="border rounded p-4">
                    <h4 id="preview-name">Plan Name</h4>
                    <div class="display-5 fw-bold text-primary my-3">
                        ₹<span id="preview-price">0</span>
                        <small class="fs-6 text-muted">/<span id="preview-cycle">month</span></small>
                    </div>
                    <p class="text-muted" id="preview-description">Plan description</p>
                    <hr>
                    <ul class="list-unstyled text-start" id="preview-features">
                        <li class="text-muted"><i class="bi bi-check2 me-2"></i> No features selected</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const nameInput = document.getElementById('name');
        const priceInput = document.getElementById('price');
        const cycleInput = document.getElementById('billing_cycle');
        const descInput = document.getElementById('description');

        function updatePreview() {
            document.getElementById('preview-name').textContent = nameInput.value || 'Plan Name';
            document.getElementById('preview-price').textContent = parseFloat(priceInput.value || 0).toFixed(2);
            document.getElementById('preview-cycle').textContent = cycleInput.value === 'yearly' ? 'year' : (cycleInput.value === 'quarterly' ? 'quarter' : 'month');
            document.getElementById('preview-description').textContent = descInput.value || 'Plan description';

            const checked = document.querySelectorAll('input[name="features[]"]:checked');
            const list = document.getElementById('preview-features');
            list.innerHTML = '';
            if (checked.length === 0) {
                list.innerHTML = '<li class="text-muted"><i class="bi bi-check2 me-2"></i> No features selected</li>';
            } else {
                checked.forEach(cb => {
                    const label = document.querySelector(`label[for="${cb.id}"]`);
                    const li = document.createElement('li');
                    li.className = 'mb-1';
                    li.innerHTML = '<i class="bi bi-check2 text-success me-2"></i>' + label.childNodes[0].textContent.trim();
                    list.appendChild(li);
                });
            }
        }

        nameInput.addEventListener('input', updatePreview);
        priceInput.addEventListener('input', updatePreview);
        cycleInput.addEventListener('change', updatePreview);
        descInput.addEventListener('input', updatePreview);
        document.querySelectorAll('input[name="features[]"]').forEach(cb => cb.addEventListener('change', updatePreview));

        // Tax Toggle Logic
        window.toggleTaxInput = function() {
            const isEnabled = document.getElementById('tax_enabled').checked;
            document.getElementById('tax-input-container').style.display = isEnabled ? 'block' : 'none';
        }
        toggleTaxInput(); // Initial State

        updatePreview();
    });
</script>
@endpush
@endsection