@extends('layouts.store-owner')

@section('title', 'Repair Job ' . $repairJob->ticket_number)
@section('page-title', 'Repair Job Details')

@push('styles')
<style>
    .status-timeline {
        position: relative;
        padding-left: 30px;
    }
    .status-timeline::before {
        content: '';
        position: absolute;
        left: 10px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e5e7eb;
    }
    .status-item {
        position: relative;
        padding-bottom: 1.5rem;
    }
    .status-item::before {
        content: '';
        position: absolute;
        left: -24px;
        top: 4px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #6b7280;
        border: 2px solid white;
        box-shadow: 0 0 0 3px #e5e7eb;
    }
    .status-item.current::before {
        background: #3b82f6;
        box-shadow: 0 0 0 3px #93c5fd;
    }
    .device-info-card {
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        color: white;
        border-radius: 12px;
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <div class="d-flex align-items-center gap-3 mb-2">
            <h4 class="mb-0">{{ $repairJob->ticket_number }}</h4>
            <span class="badge bg-{{ $repairJob->status_color }} fs-6">{{ $repairJob->status_label }}</span>
            <span class="badge bg-{{ $repairJob->priority_color }}">{{ $repairJob->priority_label }} Priority</span>
            @if($repairJob->is_overdue)
            <span class="badge bg-danger">OVERDUE</span>
            @endif
        </div>
        <p class="text-muted mb-0">Created {{ $repairJob->created_at->format('M d, Y h:i A') }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('store-owner.repair-jobs.print', $repairJob) }}" class="btn btn-outline-dark" target="_blank">
            <i class="bi bi-printer me-1"></i> Print
        </a>
        @if(auth()->user()->isStoreOwner() || auth()->user()->hasStaffPermission('manage_repair_jobs'))
        <a href="{{ route('store-owner.repair-jobs.edit', $repairJob) }}" class="btn btn-outline-primary">
            <i class="bi bi-pencil me-1"></i> Edit
        </a>
        @endif
        <a href="{{ route('store-owner.repair-jobs.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

<div class="row">
    <!-- Main Content -->
    <div class="col-lg-8">
        <!-- Device Info Card -->
        <div class="device-info-card p-4 mb-4">
            <div class="row">
                <div class="col-md-6">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-white bg-opacity-10 rounded-3 p-3 me-3">
                            <i class="bi bi-{{ $repairJob->device_type == 'phone' ? 'phone' : ($repairJob->device_type == 'laptop' ? 'laptop' : 'device-hdd') }} display-6"></i>
                        </div>
                        <div>
                            <div class="text-white-50 small">{{ $repairJob->device_type_label }}</div>
                            <div class="fs-4 fw-bold">{{ $repairJob->device_name }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="row text-white-50 small">
                        @if($repairJob->imei_serial)
                        <div class="col-6 mb-2">
                            <div>IMEI/Serial</div>
                            <div class="text-white">{{ $repairJob->imei_serial }}</div>
                        </div>
                        @endif
                        @if($repairJob->device_color)
                        <div class="col-6 mb-2">
                            <div>Color</div>
                            <div class="text-white">{{ $repairJob->device_color }}</div>
                        </div>
                        @endif
                        @if($repairJob->device_accessories)
                        <div class="col-12">
                            <div>Accessories</div>
                            <div class="text-white">{{ implode(', ', $repairJob->device_accessories) }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Issue Description -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Issue Description</h6>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $repairJob->issue_description }}</p>
            </div>
        </div>

        <!-- Diagnosis & Repair Notes -->
        @if($repairJob->diagnosis_notes || $repairJob->repair_notes)
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-journal-text me-2"></i>Technician Notes</h6>
            </div>
            <div class="card-body">
                @if($repairJob->diagnosis_notes)
                <div class="mb-3">
                    <strong class="text-muted d-block mb-1">Diagnosis</strong>
                    <p class="mb-0">{{ $repairJob->diagnosis_notes }}</p>
                </div>
                @endif
                @if($repairJob->repair_notes)
                <div>
                    <strong class="text-muted d-block mb-1">Repair Notes</strong>
                    <p class="mb-0">{{ $repairJob->repair_notes }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Parts Used -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-cpu me-2"></i>Parts Used</h6>
                @if(auth()->user()->isStoreOwner() || auth()->user()->hasStaffPermission('add_repair_parts'))
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addPartModal">
                    <i class="bi bi-plus me-1"></i> Add Part
                </button>
                @endif
            </div>
            <div class="card-body">
                @if($repairJob->parts->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Part</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end">Total</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($repairJob->parts as $part)
                            <tr>
                                <td>{{ $part->part_name }}</td>
                                <td class="text-center">{{ $part->quantity }}</td>
                                <td class="text-end">{{ $store->currency ?? '₹' }}{{ number_format($part->unit_price, 2) }}</td>
                                <td class="text-end">{{ $store->currency ?? '₹' }}{{ number_format($part->total_price, 2) }}</td>
                                <td class="text-end">
                                    @if(auth()->user()->isStoreOwner() || auth()->user()->hasStaffPermission('add_repair_parts'))
                                    <form action="{{ route('store-owner.repair-jobs.remove-part', [$repairJob, $part]) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-link text-danger p-0" onclick="return confirm('Remove this part?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Parts Total:</th>
                                <th class="text-end">{{ $store->currency ?? '₹' }}{{ number_format($repairJob->parts_cost, 2) }}</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @else
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-box display-4 d-block mb-2"></i>
                    No parts added yet
                </div>
                @endif
            </div>
        </div>

        <!-- Status Update -->
        @if(auth()->user()->isStoreOwner() || auth()->user()->hasStaffPermission('update_job_status'))
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-arrow-repeat me-2"></i>Update Status</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('store-owner.repair-jobs.update-status', $repairJob) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="row g-3">
                        <div class="col-md-4">
                            <select name="status" class="form-select" required>
                                <option value="">Select New Status</option>
                                @foreach(\App\Models\RepairJob::STATUS_TRANSITIONS[$repairJob->status] ?? [] as $nextStatus)
                                <option value="{{ $nextStatus }}">{{ \App\Models\RepairJob::STATUSES[$nextStatus]['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <input type="text" name="notes" class="form-control" placeholder="Add a note (optional)">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">Update Status</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Customer Info -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-person me-2"></i>Customer</h6>
            </div>
            <div class="card-body">
                @if($repairJob->customer)
                <div class="fw-bold mb-1">{{ $repairJob->customer->name }}</div>
                @if($repairJob->customer->phone)
                <div class="mb-1">
                    <a href="tel:{{ $repairJob->customer->phone }}"><i class="bi bi-telephone me-1"></i>{{ $repairJob->customer->phone }}</a>
                </div>
                @endif
                @if($repairJob->customer->email)
                <div><a href="mailto:{{ $repairJob->customer->email }}"><i class="bi bi-envelope me-1"></i>{{ $repairJob->customer->email }}</a></div>
                @endif
                @else
                <span class="text-muted">Walk-in Customer</span>
                @endif
            </div>
        </div>

        <!-- Technician Assignment -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-person-badge me-2"></i>Technician</h6>
            </div>
            <div class="card-body">
                @if($repairJob->technician)
                <div class="d-flex align-items-center">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                        {{ strtoupper(substr($repairJob->technician->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="fw-bold">{{ $repairJob->technician->name }}</div>
                        <small class="text-muted">{{ $repairJob->technician->role_name }}</small>
                    </div>
                </div>
                @else
                <span class="text-muted">Not assigned</span>
                @endif
                
                @if(auth()->user()->isStoreOwner() || auth()->user()->hasStaffPermission('manage_repair_jobs'))
                <form action="{{ route('store-owner.repair-jobs.assign', $repairJob) }}" method="POST" class="mt-3">
                    @csrf
                    @method('PATCH')
                    <div class="input-group">
                        <select name="assigned_technician_id" class="form-select form-select-sm">
                            <option value="">Unassigned</option>
                            @foreach($technicians as $tech)
                            <option value="{{ $tech->id }}" {{ $repairJob->assigned_technician_id == $tech->id ? 'selected' : '' }}>{{ $tech->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-sm btn-outline-primary">Assign</button>
                    </div>
                </form>
                @endif
            </div>
        </div>

        <!-- Pricing Summary -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-currency-rupee me-2"></i>Pricing</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Estimated Cost</span>
                    <span>{{ $store->currency ?? '₹' }}{{ number_format($repairJob->estimated_cost ?? 0, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Parts Cost</span>
                    <span>{{ $store->currency ?? '₹' }}{{ number_format($repairJob->parts_cost, 2) }}</span>
                </div>
                @if($repairJob->final_cost)
                <div class="d-flex justify-content-between mb-2">
                    <span>Final Cost</span>
                    <span class="fw-bold">{{ $store->currency ?? '₹' }}{{ number_format($repairJob->final_cost, 2) }}</span>
                </div>
                @endif
                <hr>
                <div class="d-flex justify-content-between mb-2 text-success">
                    <span>Advance Paid</span>
                    <span>- {{ $store->currency ?? '₹' }}{{ number_format($repairJob->advance_paid, 2) }}</span>
                </div>
                @if($repairJob->discount > 0)
                <div class="d-flex justify-content-between mb-2 text-success">
                    <span>Discount</span>
                    <span>- {{ $store->currency ?? '₹' }}{{ number_format($repairJob->discount, 2) }}</span>
                </div>
                @endif
                <hr>
                <div class="d-flex justify-content-between fw-bold fs-5">
                    <span>Balance Due</span>
                    <span>{{ $store->currency ?? '₹' }}{{ number_format($repairJob->balance_due, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Warranty Info -->
        @if($repairJob->warranty_days > 0)
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-shield-check me-2"></i>Warranty</h6>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <strong>{{ $repairJob->warranty_days }} Days</strong>
                </div>
                @if($repairJob->warranty_until)
                <div class="{{ $repairJob->is_under_warranty ? 'text-success' : 'text-danger' }}">
                    <i class="bi bi-{{ $repairJob->is_under_warranty ? 'check-circle' : 'x-circle' }} me-1"></i>
                    {{ $repairJob->is_under_warranty ? 'Valid until' : 'Expired on' }} {{ $repairJob->warranty_until->format('M d, Y') }}
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Status Timeline -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Timeline</h6>
            </div>
            <div class="card-body">
                <div class="status-timeline">
                    @foreach($repairJob->statusLogs as $index => $log)
                    <div class="status-item {{ $index === 0 ? 'current' : '' }}">
                        <div class="fw-bold">{{ $log->new_status_label }}</div>
                        <div class="text-muted small">{{ $log->created_at->format('M d, Y h:i A') }}</div>
                        @if($log->changedBy)
                        <div class="text-muted small">by {{ $log->changedBy->name }}</div>
                        @endif
                        @if($log->notes)
                        <div class="mt-1 small fst-italic">"{{ $log->notes }}"</div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Part Modal -->
<div class="modal fade" id="addPartModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('store-owner.repair-jobs.add-part', $repairJob) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Part</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select Part</label>
                        <select name="product_id" class="form-select" required id="partSelect">
                            <option value="">Choose a part...</option>
                            @foreach($spareParts as $part)
                            <option value="{{ $part->id }}" data-price="{{ $part->price }}" data-stock="{{ $part->track_inventory ? $part->stock_quantity : 'N/A' }}">
                                {{ $part->name }} (Stock: {{ $part->track_inventory ? $part->stock_quantity : 'N/A' }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Quantity</label>
                            <input type="number" name="quantity" class="form-control" value="1" min="1" required>
                        </div>
                        <div class="col">
                            <label class="form-label">Unit Price</label>
                            <input type="number" step="0.01" name="unit_price" class="form-control" id="unitPriceInput" placeholder="Auto-filled">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <input type="text" name="notes" class="form-control" placeholder="Optional notes">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Part</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('partSelect').addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    const price = selected.dataset.price;
    if (price) {
        document.getElementById('unitPriceInput').value = price;
    }
});
</script>
@endpush
