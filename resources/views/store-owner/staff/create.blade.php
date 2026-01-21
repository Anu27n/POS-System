@extends('layouts.store-owner')

@section('title', 'Add Staff')
@section('page-title', 'Add Staff Member')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Staff Details</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('store-owner.staff.store') }}" method="POST">
                    @csrf

                    <!-- Basic Info -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                name="name" value="{{ old('name') }}" required>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-select @error('role') is-invalid @enderror" name="role" required>
                                @foreach($roles as $value => $label)
                                <option value="{{ $value }}" {{ old('role') === $value ? 'selected' : '' }}>{{ $label }}</option>
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
                                name="email" value="{{ old('email') }}">
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                name="phone" value="{{ old('phone') }}">
                            @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Create Account Option -->
                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="create_account"
                                    id="createAccount" value="1" {{ old('create_account') ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="createAccount">
                                    Create login account for this staff member
                                </label>
                                <div class="text-muted small">
                                    This will allow the staff member to login and access the store dashboard based on their role permissions.
                                </div>
                            </div>

                            <div id="accountFields" style="{{ old('create_account') ? '' : 'display: none;' }}">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label">Password <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                                            name="password" minlength="8">
                                        @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control"
                                            name="password_confirmation">
                                    </div>
                                </div>
                                <small class="text-muted">Note: Email is required to create a login account.</small>
                            </div>
                        </div>
                    </div>

                    <!-- Custom Permissions (Optional) -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Custom Permissions (Optional)</h6>
                            <small class="text-muted">Leave empty to use role defaults</small>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @foreach($permissions as $value => $label)
                                <div class="col-md-6 col-lg-4">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox"
                                            name="permissions[]" value="{{ $value }}" id="perm_{{ $value }}"
                                            {{ in_array($value, old('permissions', [])) ? 'checked' : '' }}>
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
                            <i class="bi bi-check-lg me-1"></i> Add Staff Member
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('createAccount').addEventListener('change', function() {
        document.getElementById('accountFields').style.display = this.checked ? 'block' : 'none';
    });
</script>
@endpush
@endsection