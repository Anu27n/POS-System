@extends('layouts.store-owner')

@section('title', 'Create Repair Job')
@section('page-title', 'Create Repair Job')

@section('content')
<div class="row">
    <div class="col-lg-10 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-tools me-2"></i>New Repair Job</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('store-owner.repair-jobs.store') }}" method="POST">
                    @csrf
                    
                    <!-- Customer Section -->
                    <div class="border-bottom pb-4 mb-4">
                        <h6 class="text-muted mb-3"><i class="bi bi-person me-2"></i>Customer Information</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Select Existing Customer</label>
                                <select name="store_customer_id" id="customerSelect" class="form-select">
                                    <option value="">-- New Customer --</option>
                                    @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ old('store_customer_id') == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }} ({{ $customer->phone }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12" id="newCustomerFields">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Customer Name <span class="text-danger">*</span></label>
                                        <input type="text" name="customer_name" class="form-control @error('customer_name') is-invalid @enderror" value="{{ old('customer_name') }}">
                                        @error('customer_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Phone <span class="text-danger">*</span></label>
                                        <input type="text" name="customer_phone" class="form-control @error('customer_phone') is-invalid @enderror" value="{{ old('customer_phone') }}">
                                        @error('customer_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="customer_email" class="form-control" value="{{ old('customer_email') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Device Section -->
                    <div class="border-bottom pb-4 mb-4">
                        <h6 class="text-muted mb-3"><i class="bi bi-phone me-2"></i>Device Information</h6>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Device Type <span class="text-danger">*</span></label>
                                <select name="device_type" class="form-select @error('device_type') is-invalid @enderror">
                                    @foreach(\App\Models\RepairJob::DEVICE_TYPES as $key => $label)
                                    <option value="{{ $key }}" {{ old('device_type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('device_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Brand</label>
                                <input type="text" name="device_brand" class="form-control" value="{{ old('device_brand') }}" placeholder="e.g. Apple, Samsung">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Model</label>
                                <input type="text" name="device_model" class="form-control" value="{{ old('device_model') }}" placeholder="e.g. iPhone 14 Pro">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Color</label>
                                <input type="text" name="device_color" class="form-control" value="{{ old('device_color') }}" placeholder="e.g. Black">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">IMEI / Serial Number</label>
                                <input type="text" name="imei_serial" class="form-control" value="{{ old('imei_serial') }}" placeholder="For tracking & warranty">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Device Password/Pattern (Optional)</label>
                                <input type="text" name="device_password" class="form-control" value="{{ old('device_password') }}" placeholder="For testing after repair">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Accessories Received</label>
                                <div class="d-flex flex-wrap gap-3">
                                    @foreach(['Charger', 'Case', 'SIM Card', 'Memory Card', 'Box', 'Earphones'] as $accessory)
                                    <div class="form-check">
                                        <input type="checkbox" name="device_accessories[]" value="{{ $accessory }}" class="form-check-input" id="acc_{{ Str::slug($accessory) }}"
                                            {{ in_array($accessory, old('device_accessories', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="acc_{{ Str::slug($accessory) }}">{{ $accessory }}</label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Issue Section -->
                    <div class="border-bottom pb-4 mb-4">
                        <h6 class="text-muted mb-3"><i class="bi bi-exclamation-triangle me-2"></i>Issue Details</h6>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Issue Description <span class="text-danger">*</span></label>
                                <textarea name="issue_description" rows="3" class="form-control @error('issue_description') is-invalid @enderror" 
                                    placeholder="Describe the problem reported by the customer...">{{ old('issue_description') }}</textarea>
                                @error('issue_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Priority</label>
                                <select name="priority" class="form-select">
                                    @foreach(\App\Models\RepairJob::PRIORITIES as $key => $priority)
                                    <option value="{{ $key }}" {{ old('priority', 'normal') == $key ? 'selected' : '' }}>{{ $priority['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Assign Technician</label>
                                <select name="assigned_technician_id" class="form-select">
                                    <option value="">-- Assign Later --</option>
                                    @foreach($technicians as $tech)
                                    <option value="{{ $tech->id }}" {{ old('assigned_technician_id') == $tech->id ? 'selected' : '' }}>{{ $tech->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Expected Delivery</label>
                                <input type="datetime-local" name="expected_delivery_at" class="form-control" value="{{ old('expected_delivery_at') }}">
                            </div>
                        </div>
                    </div>

                    <!-- Pricing Section -->
                    <div class="border-bottom pb-4 mb-4">
                        <h6 class="text-muted mb-3"><i class="bi bi-currency-rupee me-2"></i>Pricing</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Estimated Cost</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" step="0.01" name="estimated_cost" class="form-control" value="{{ old('estimated_cost') }}" placeholder="0.00">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Advance Paid</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" step="0.01" name="advance_paid" class="form-control" value="{{ old('advance_paid', 0) }}" placeholder="0.00">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Warranty Days</label>
                                <select name="warranty_days" class="form-select">
                                    <option value="0">No Warranty</option>
                                    <option value="7" {{ old('warranty_days') == 7 ? 'selected' : '' }}>7 Days</option>
                                    <option value="15" {{ old('warranty_days') == 15 ? 'selected' : '' }}>15 Days</option>
                                    <option value="30" {{ old('warranty_days') == 30 ? 'selected' : '' }}>30 Days</option>
                                    <option value="60" {{ old('warranty_days') == 60 ? 'selected' : '' }}>60 Days</option>
                                    <option value="90" {{ old('warranty_days') == 90 ? 'selected' : '' }}>90 Days</option>
                                    <option value="180" {{ old('warranty_days') == 180 ? 'selected' : '' }}>6 Months</option>
                                    <option value="365" {{ old('warranty_days') == 365 ? 'selected' : '' }}>1 Year</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Internal Notes -->
                    <div class="mb-4">
                        <label class="form-label">Internal Notes (Staff Only)</label>
                        <textarea name="internal_notes" rows="2" class="form-control" placeholder="Notes visible only to staff...">{{ old('internal_notes') }}</textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('store-owner.repair-jobs.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> Create Repair Job
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('customerSelect').addEventListener('change', function() {
    const newCustomerFields = document.getElementById('newCustomerFields');
    if (this.value) {
        newCustomerFields.style.display = 'none';
    } else {
        newCustomerFields.style.display = 'block';
    }
});

// Initial state
if (document.getElementById('customerSelect').value) {
    document.getElementById('newCustomerFields').style.display = 'none';
}
</script>
@endpush
