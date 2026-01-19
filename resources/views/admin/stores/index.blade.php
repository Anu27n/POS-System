@extends('layouts.admin')

@section('title', 'Stores')
@section('page-title', 'Store Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <form class="d-flex gap-2" method="GET">
            <input type="text" name="search" class="form-control" placeholder="Search stores..." 
                   value="{{ request('search') }}" style="width: 250px;">
            <select name="status" class="form-select" style="width: 150px;">
                <option value="">All Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            <button type="submit" class="btn btn-outline-primary">Filter</button>
        </form>
    </div>
    <a href="{{ route('admin.stores.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Add Store
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Store</th>
                        <th>Owner</th>
                        <th>Type</th>
                        <th>Products</th>
                        <th>Orders</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stores as $store)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" 
                                     style="width: 35px; height: 35px; font-size: 0.85rem;">
                                    {{ strtoupper(substr($store->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $store->name }}</div>
                                    <small class="text-muted">{{ $store->slug }}</small>
                                </div>
                            </div>
                        </td>
                        <td>{{ $store->owner->name ?? 'No owner' }}</td>
                        <td><span class="badge bg-secondary">{{ ucfirst($store->type) }}</span></td>
                        <td>{{ $store->products_count ?? $store->products()->count() }}</td>
                        <td>{{ $store->orders_count ?? $store->orders()->count() }}</td>
                        <td>
                            <span class="badge bg-{{ $store->status === 'active' ? 'success' : 'secondary' }}">
                                {{ ucfirst($store->status) }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('admin.stores.show', $store) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.stores.edit', $store) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.stores.toggle-status', $store) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-{{ $store->status === 'active' ? 'warning' : 'success' }}"
                                            title="{{ $store->status === 'active' ? 'Deactivate' : 'Activate' }}">
                                        <i class="bi bi-{{ $store->status === 'active' ? 'pause' : 'play' }}"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.stores.destroy', $store) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Are you sure you want to delete this store?');">
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
                        <td colspan="7" class="text-center py-4 text-muted">No stores found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-4">
    {{ $stores->links() }}
</div>
@endsection
