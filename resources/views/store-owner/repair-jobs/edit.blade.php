@extends('layouts.store-owner')

@section('title', 'Edit Repair Job')
@section('page-title', 'Edit Repair Job')

@section('content')
<div class="row">
    <div class="col-lg-10 mx-auto">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-pencil me-2"></i>Edit Repair Job - {{ $repairJob->ticket_number }}</h5>
                <span class="badge bg-{{ $repairJob->status_color }}">{{ $repairJob->status_label }}</span>
            </div>
            <div class="card-body">
                <form action="{{ route('store-owner.repair-jobs.update', $repairJob) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <!-- Customer Section -->
                    <div class="border-bottom pb-4 mb-4">
                        <h6 class="text-muted mb-3"><i class="bi bi-person me-2"></i>Customer Information</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Customer</label>
                                <select name="store_customer_id" class="form-select">
                                    <option value="">-- No Customer --</option>
                                    @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ old('store_customer_id', $repairJob->store_customer_id) == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }} ({{ $customer->phone }})
                                    </option>
                                    @endforeach
                                </select>
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
                                    <option value="{{ $key }}" {{ old('device_type', $repairJob->device_type) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('device_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Brand</label>
                                <input type="text" name="device_brand" class="form-control" value="{{ old('device_brand', $repairJob->device_brand) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Model</label>
                                <input type="text" name="device_model" class="form-control" value="{{ old('device_model', $repairJob->device_model) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Color</label>
                                <input type="text" name="device_color" class="form-control" value="{{ old('device_color', $repairJob->device_color) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">IMEI / Serial Number</label>
                                <input type="text" name="imei_serial" class="form-control" value="{{ old('imei_serial', $repairJob->imei_serial) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Device Password/Pattern</label>
                                <input type="text" name="device_password" class="form-control" value="{{ old('device_password', $repairJob->device_password) }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Accessories Received</label>
                                <div class="d-flex flex-wrap gap-3">
                                    @php $currentAccessories = old('device_accessories', $repairJob->device_accessories ?? []); @endphp
                                    @foreach(['Charger', 'Case', 'SIM Card', 'Memory Card', 'Box', 'Earphones'] as $accessory)
                                    <div class="form-check">
                                        <input type="checkbox" name="device_accessories[]" value="{{ $accessory }}" class="form-check-input" id="acc_{{ Str::slug($accessory) }}"
                                            {{ in_array($accessory, $currentAccessories) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="acc_{{ Str::slug($accessory) }}">{{ $accessory }}</label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Issue Section -->
                    <div class="border-bottom pb-4 mb-4">
                        <h6 class="text-muted mb-3"><i class="bi bi-exclamation-triangle me-2"></i>Issue & Repair Details</h6>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Issue Description <span class="text-danger">*</span></label>
                                <textarea name="issue_description" rows="3" class="form-control @error('issue_description') is-invalid @enderror">{{ old('issue_description', $repairJob->issue_description) }}</textarea>
                                @error('issue_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Diagnosis Notes</label>
                                <textarea name="diagnosis_notes" rows="2" class="form-control">{{ old('diagnosis_notes', $repairJob->diagnosis_notes) }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Repair Notes</label>
                                <textarea name="repair_notes" rows="2" class="form-control">{{ old('repair_notes', $repairJob->repair_notes) }}</textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Priority</label>
                                <select name="priority" class="form-select">
                                    @foreach(\App\Models\RepairJob::PRIORITIES as $key => $priority)
                                    <option value="{{ $key }}" {{ old('priority', $repairJob->priority) == $key ? 'selected' : '' }}>{{ $priority['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Assign Technician</label>
                                <select name="assigned_technician_id" class="form-select">
                                    <option value="">-- Unassigned --</option>
                                    @foreach($technicians as $tech)
                                    <option value="{{ $tech->id }}" {{ old('assigned_technician_id', $repairJob->assigned_technician_id) == $tech->id ? 'selected' : '' }}>{{ $tech->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Expected Delivery</label>
                                <input type="datetime-local" name="expected_delivery_at" class="form-control" 
                                    value="{{ old('expected_delivery_at', $repairJob->expected_delivery_at ? $repairJob->expected_delivery_at->format('Y-m-d\TH:i') : '') }}">
                            </div>
                        </div>
                    </div>

                    <!-- Pricing Section -->
                    <div class="border-bottom pb-4 mb-4">
                        <h6 class="text-muted mb-3"><i class="bi bi-currency-rupee me-2"></i>Pricing</h6>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Estimated Cost</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" step="0.01" name="estimated_cost" class="form-control" value="{{ old('estimated_cost', $repairJob->estimated_cost) }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Final Cost</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" step="0.01" name="final_cost" class="form-control" value="{{ old('final_cost', $repairJob->final_cost) }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Advance Paid</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" step="0.01" name="advance_paid" class="form-control" value="{{ old('advance_paid', $repairJob->advance_paid) }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Discount</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" step="0.01" name="discount" class="form-control" value="{{ old('discount', $repairJob->discount) }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Warranty Days</label>
                                <select name="warranty_days" class="form-select">
                                    <option value="0" {{ old('warranty_days', $repairJob->warranty_days) == 0 ? 'selected' : '' }}>No Warranty</option>
                                    <option value="7" {{ old('warranty_days', $repairJob->warranty_days) == 7 ? 'selected' : '' }}>7 Days</option>
                                    <option value="15" {{ old('warranty_days', $repairJob->warranty_days) == 15 ? 'selected' : '' }}>15 Days</option>
                                    <option value="30" {{ old('warranty_days', $repairJob->warranty_days) == 30 ? 'selected' : '' }}>30 Days</option>
                                    <option value="60" {{ old('warranty_days', $repairJob->warranty_days) == 60 ? 'selected' : '' }}>60 Days</option>
                                    <option value="90" {{ old('warranty_days', $repairJob->warranty_days) == 90 ? 'selected' : '' }}>90 Days</option>
                                    <option value="180" {{ old('warranty_days', $repairJob->warranty_days) == 180 ? 'selected' : '' }}>6 Months</option>
                                    <option value="365" {{ old('warranty_days', $repairJob->warranty_days) == 365 ? 'selected' : '' }}>1 Year</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Internal Notes -->
                    <div class="mb-4">
                        <label class="form-label">Internal Notes (Staff Only)</label>
                        <textarea name="internal_notes" rows="2" class="form-control">{{ old('internal_notes', $repairJob->internal_notes) }}</textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('store-owner.repair-jobs.show', $repairJob) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> Update Repair Job
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
