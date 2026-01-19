@extends('layouts.admin')

@section('title', 'Users')
@section('page-title', 'User Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <form class="d-flex gap-2" method="GET">
            <input type="text" name="search" class="form-control" placeholder="Search users..." 
                   value="{{ request('search') }}" style="width: 250px;">
            <select name="role" class="form-select" style="width: 150px;">
                <option value="">All Roles</option>
                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="store_owner" {{ request('role') === 'store_owner' ? 'selected' : '' }}>Store Owner</option>
                <option value="customer" {{ request('role') === 'customer' ? 'selected' : '' }}>Customer</option>
            </select>
            <button type="submit" class="btn btn-outline-primary">Filter</button>
        </form>
    </div>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Add User
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-2" 
                                     style="width: 35px; height: 35px; font-size: 0.85rem;">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <span class="fw-semibold">{{ $user->name }}</span>
                            </div>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @php
                                $roleColors = [
                                    'admin' => 'danger',
                                    'store_owner' => 'primary',
                                    'customer' => 'secondary',
                                ];
                            @endphp
                            <span class="badge bg-{{ $roleColors[$user->role] ?? 'secondary' }}">
                                {{ str_replace('_', ' ', ucfirst($user->role)) }}
                            </span>
                        </td>
                        <td>{{ $user->phone ?? '-' }}</td>
                        <td>
                            <span class="badge bg-{{ $user->is_active ? 'success' : 'secondary' }}">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>{{ $user->created_at->format('M d, Y') }}</td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @if($user->id !== auth()->id())
                                    <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-{{ $user->is_active ? 'warning' : 'success' }}"
                                                title="{{ $user->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="bi bi-{{ $user->is_active ? 'pause' : 'play' }}"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to delete this user?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No users found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-4">
    {{ $users->links() }}
</div>
@endsection
