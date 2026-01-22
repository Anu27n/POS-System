@extends('layouts.store-owner')

@section('title', 'Tax Settings')
@section('page-title', 'Tax Settings')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <!-- Tax Settings Card -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Tax Configuration</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('store-owner.tax-settings.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="taxes_enabled" name="taxes_enabled" value="1" 
                                {{ old('taxes_enabled', $taxSettings->taxes_enabled) ? 'checked' : '' }}>
                            <label class="form-check-label" for="taxes_enabled">
                                <strong>Enable Taxes</strong>
                                <small class="text-muted d-block">Turn on GST/VAT taxation for your store</small>
                            </label>
                        </div>
                    </div>

                    <div class="tax-settings-fields" style="{{ !$taxSettings->taxes_enabled ? 'opacity: 0.5' : '' }}">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="tax_type" class="form-label">Tax Calculation Method</label>
                                <select class="form-select @error('tax_type') is-invalid @enderror" id="tax_type" name="tax_type">
                                    <option value="order_level" {{ old('tax_type', $taxSettings->tax_type) === 'order_level' ? 'selected' : '' }}>
                                        Order Level Tax
                                    </option>
                                    <option value="item_level" {{ old('tax_type', $taxSettings->tax_type) === 'item_level' ? 'selected' : '' }}>
                                        Item Level Tax
                                    </option>
                                </select>
                                <small class="text-muted">
                                    <strong>Order Level:</strong> Tax calculated on total order value<br>
                                    <strong>Item Level:</strong> Tax calculated on each item separately
                                </small>
                            </div>
                            <div class="col-md-6">
                                <label for="tax_number" class="form-label">GST/Tax Registration Number</label>
                                <input type="text" class="form-control @error('tax_number') is-invalid @enderror" 
                                       id="tax_number" name="tax_number" 
                                       value="{{ old('tax_number', $taxSettings->tax_number) }}"
                                       placeholder="e.g., 22AAAAA0000A1Z5">
                                @error('tax_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="show_tax_on_receipt" name="show_tax_on_receipt" value="1"
                                        {{ old('show_tax_on_receipt', $taxSettings->show_tax_on_receipt) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="show_tax_on_receipt">
                                        Show tax breakdown on receipt
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="tax_inclusive_pricing" name="tax_inclusive_pricing" value="1"
                                        {{ old('tax_inclusive_pricing', $taxSettings->tax_inclusive_pricing) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="tax_inclusive_pricing">
                                        Prices are tax inclusive
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tax List Card -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Tax Rates</h5>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addTaxModal">
                    <i class="bi bi-plus-lg me-1"></i> Add Tax
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Tax Name</th>
                                <th>Percentage</th>
                                <th>Status</th>
                                <th width="150">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($taxes as $tax)
                                <tr>
                                    <td class="fw-semibold">{{ $tax->name }}</td>
                                    <td>{{ $tax->percentage }}%</td>
                                    <td>
                                        @if($tax->is_enabled)
                                            <span class="badge bg-success">Enabled</span>
                                        @else
                                            <span class="badge bg-secondary">Disabled</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-primary" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editTaxModal{{ $tax->id }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form action="{{ route('store-owner.tax-settings.toggle-tax', $tax) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-{{ $tax->is_enabled ? 'warning' : 'success' }}">
                                                    <i class="bi bi-{{ $tax->is_enabled ? 'pause' : 'play' }}"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('store-owner.tax-settings.destroy-tax', $tax) }}" method="POST" class="d-inline" 
                                                  onsubmit="return confirm('Are you sure you want to delete this tax?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Edit Tax Modal -->
                                <div class="modal fade" id="editTaxModal{{ $tax->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('store-owner.tax-settings.update-tax', $tax) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Tax</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Tax Name <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" name="name" value="{{ $tax->name }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Percentage <span class="text-danger">*</span></label>
                                                        <div class="input-group">
                                                            <input type="number" step="0.01" class="form-control" name="percentage" value="{{ $tax->percentage }}" required>
                                                            <span class="input-group-text">%</span>
                                                        </div>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="is_enabled" value="1" {{ $tax->is_enabled ? 'checked' : '' }}>
                                                        <label class="form-check-label">Enabled</label>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary">Update Tax</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        <i class="bi bi-percent fs-1 d-block mb-2"></i>
                                        No taxes configured. Add your first tax rate.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Quick Add Common Taxes -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Quick Add GST Taxes</h5>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">Click to quickly add common Indian GST tax rates</p>
                <div class="d-grid gap-2">
                    <form action="{{ route('store-owner.tax-settings.store-tax') }}" method="POST">
                        @csrf
                        <input type="hidden" name="name" value="CGST">
                        <input type="hidden" name="percentage" value="9">
                        <button type="submit" class="btn btn-outline-primary w-100">
                            <i class="bi bi-plus me-1"></i> Add CGST (9%)
                        </button>
                    </form>
                    <form action="{{ route('store-owner.tax-settings.store-tax') }}" method="POST">
                        @csrf
                        <input type="hidden" name="name" value="SGST">
                        <input type="hidden" name="percentage" value="9">
                        <button type="submit" class="btn btn-outline-primary w-100">
                            <i class="bi bi-plus me-1"></i> Add SGST (9%)
                        </button>
                    </form>
                    <form action="{{ route('store-owner.tax-settings.store-tax') }}" method="POST">
                        @csrf
                        <input type="hidden" name="name" value="IGST">
                        <input type="hidden" name="percentage" value="18">
                        <button type="submit" class="btn btn-outline-primary w-100">
                            <i class="bi bi-plus me-1"></i> Add IGST (18%)
                        </button>
                    </form>
                    <form action="{{ route('store-owner.tax-settings.store-tax') }}" method="POST">
                        @csrf
                        <input type="hidden" name="name" value="GST">
                        <input type="hidden" name="percentage" value="5">
                        <button type="submit" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-plus me-1"></i> Add GST (5%)
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Tax Reports</h5>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">View detailed tax collection reports</p>
                <a href="{{ route('store-owner.reports.tax') }}" class="btn btn-primary w-100">
                    <i class="bi bi-file-earmark-text me-1"></i> View Tax Reports
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Add Tax Modal -->
<div class="modal fade" id="addTaxModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('store-owner.tax-settings.store-tax') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add New Tax</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tax Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" placeholder="e.g., CGST, SGST, VAT" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Percentage <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" step="0.01" class="form-control" name="percentage" placeholder="e.g., 9" required>
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_enabled" value="1" checked>
                        <label class="form-check-label">Enable this tax immediately</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Tax</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        <div class="toast show" role="alert">
            <div class="toast-header bg-success text-white">
                <i class="bi bi-check-circle me-2"></i>
                <strong class="me-auto">Success</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">{{ session('success') }}</div>
        </div>
    </div>
@endif

@push('scripts')
<script>
document.getElementById('taxes_enabled').addEventListener('change', function() {
    document.querySelector('.tax-settings-fields').style.opacity = this.checked ? '1' : '0.5';
});
</script>
@endpush
@endsection
