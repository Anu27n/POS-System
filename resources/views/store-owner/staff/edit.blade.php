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

                    <!-- Login Account Section -->
                    <div class="card bg-light mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-person-lock me-2"></i>Login Account
                            </h6>
                        </div>
                        <div class="card-body">
                            @if($staff->user)
                            <div class="d-flex align-items-center">
                                <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <i class="bi bi-check-lg"></i>
                                </div>
                                <div>
                                    <strong>Account Active</strong>
                                    <div class="text-muted small">Login: {{ $staff->user->email }}</div>
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="reset_password" id="resetPassword" value="1">
                                    <label class="form-check-label" for="resetPassword">
                                        Reset Password
                                    </label>
                                </div>
                                <div id="resetPasswordFields" style="display: none;" class="mt-3">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label">New Password <span class="text-danger">*</span></label>
                                            <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" minlength="8">
                                            @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                            <input type="password" class="form-control" name="password_confirmation">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-muted small mt-2">
                                <i class="bi bi-info-circle me-1"></i>
                                Account status is synced with staff active status.
                            </div>
                            @else
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="create_account" id="createAccount" value="1" {{ old('create_account') ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="createAccount">
                                    Create login account for this staff member
                                </label>
                                <div class="text-muted small">
                                    This will allow the staff member to login with their email and password.
                                </div>
                            </div>
                            <div id="accountFields" style="{{ old('create_account') ? '' : 'display: none;' }}">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label">Password <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" minlength="8">
                                        @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" name="password_confirmation">
                                    </div>
                                </div>
                                <small class="text-muted mt-2 d-block">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Email is required to create a login account.
                                </small>
                            </div>
                            @endif
                        </div>
                    </div>

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

    // Toggle create account fields
    const createAccountCheckbox = document.getElementById('createAccount');
    if (createAccountCheckbox) {
        createAccountCheckbox.addEventListener('change', function() {
            document.getElementById('accountFields').style.display = this.checked ? 'block' : 'none';
        });
    }

    // Toggle reset password fields
    const resetPasswordCheckbox = document.getElementById('resetPassword');
    if (resetPasswordCheckbox) {
        resetPasswordCheckbox.addEventListener('change', function() {
            document.getElementById('resetPasswordFields').style.display = this.checked ? 'block' : 'none';
        });
    }
</script>
@endpush
@endsection