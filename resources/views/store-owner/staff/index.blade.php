@extends('layouts.store-owner')

@section('title', 'Staff Management')
@section('page-title', 'Staff Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="text-muted mb-0">Manage your store staff and their permissions</p>
    </div>
    <a href="{{ route('store-owner.staff.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Add Staff
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Name</th>
                        <th>Contact</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Account</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($staff as $member)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" 
                                     style="width: 40px; height: 40px;">
                                    {{ strtoupper(substr($member->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $member->name }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($member->email)
                                <div><i class="bi bi-envelope me-1 text-muted"></i> {{ $member->email }}</div>
                            @endif
                            @if($member->phone)
                                <div><i class="bi bi-phone me-1 text-muted"></i> {{ $member->phone }}</div>
                            @endif
                            @if(!$member->email && !$member->phone)
                                <span class="text-muted">Not provided</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-primary">{{ $member->role_name }}</span>
                        </td>
                        <td>
                            @if($member->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td>
                            @if($member->user)
                                <span class="badge bg-info">Has Login</span>
                            @else
                                <span class="text-muted">No account</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <div class="btn-group">
                                <a href="{{ route('store-owner.staff.edit', $member) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('store-owner.staff.toggle-status', $member) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-{{ $member->is_active ? 'warning' : 'success' }}"
                                            title="{{ $member->is_active ? 'Deactivate' : 'Activate' }}">
                                        <i class="bi bi-{{ $member->is_active ? 'pause' : 'play' }}"></i>
                                    </button>
                                </form>
                                <form action="{{ route('store-owner.staff.destroy', $member) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Are you sure you want to remove this staff member?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="bi bi-person-badge fs-1 text-muted d-block mb-3"></i>
                            <h5>No staff members yet</h5>
                            <p class="text-muted">Add staff members to help manage your store</p>
                            <a href="{{ route('store-owner.staff.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-lg me-1"></i> Add First Staff
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Role Permissions Info -->
<div class="card mt-4">
    <div class="card-header">
        <h6 class="mb-0">Role Permissions Reference</h6>
    </div>
    <div class="card-body">
        <div class="row">
            @foreach(\App\Models\Staff::ROLE_PERMISSIONS as $role => $permissions)
            <div class="col-md-6 col-lg-3 mb-3">
                <h6 class="fw-semibold">{{ \App\Models\Staff::ROLES[$role] }}</h6>
                <ul class="list-unstyled small">
                    @foreach($permissions as $permission)
                    <li><i class="bi bi-check text-success me-1"></i>{{ \App\Models\Staff::PERMISSIONS[$permission] }}</li>
                    @endforeach
                </ul>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
