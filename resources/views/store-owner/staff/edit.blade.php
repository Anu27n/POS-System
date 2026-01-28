@extends('layouts.store-owner')

@section('title', 'Edit Staff')
@section('page-title', 'Edit Staff Member')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Edit Staff Details</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('store-owner.staff.update', $staff) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Basic Info -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                name="name" value="{{ old('name', $staff->name) }}" required>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-select @error('role') is-invalid @enderror" name="role" required>
                                @foreach($roles as $value => $label)
                                <option value="{{ $value }}" {{ old('role', $staff->role) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                name="email" value="{{ old('email', $staff->email) }}">
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                name="phone" value="{{ old('phone', $staff->phone) }}">
                            @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active"
                                id="isActive" value="1" {{ old('is_active', $staff->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="isActive">Active</label>
                        </div>
                    </div>

                    @if($staff->user)
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        This staff member has a login account. Their account status will be synced with staff status.
                    </div>
                    @endif

                    <!-- Custom Permissions -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Custom Permissions</h6>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="resetToDefaults()">
                                Reset to Role Defaults
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @php $currentPermissions = old('permissions', $staff->permissions ?? \App\Models\Staff::ROLE_PERMISSIONS[$staff->role] ?? []) @endphp
                                @foreach($permissions as $value => $label)
                                <div class="col-md-6 col-lg-4">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input permission-checkbox" type="checkbox"
                                            name="permissions[]" value="{{ $value }}" id="perm_{{ $value }}"
                                            {{ in_array($value, $currentPermissions) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="perm_{{ $value }}">{{ $label }}</label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('store-owner.staff.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> Update Staff Member
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const rolePermissions = @json(\App\Models\Staff::ROLE_PERMISSIONS);

    function resetToDefaults() {
        const role = document.querySelector('select[name="role"]').value;
        const defaults = rolePermissions[role] || [];

        document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
            checkbox.checked = defaults.includes(checkbox.value);
        });
    }
</script>
@endpush
@endsection