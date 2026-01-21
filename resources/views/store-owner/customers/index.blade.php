@extends('layouts.store-owner')

@section('title', 'Customers')
@section('page-title', 'Customer List')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="text-muted mb-0">View and manage your store customers</p>
    </div>
    <a href="{{ route('store-owner.customers.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Add Customer
    </a>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-6">
                <label class="form-label">Search</label>
                <input type="text" class="form-control" name="search"
                    value="{{ request('search') }}" placeholder="Search by name, email or phone...">
            </div>
            <div class="col-md-4">
                <label class="form-label">Filter</label>
                <select class="form-select" name="filter" onchange="this.form.submit()">
                    <option value="">All Customers</option>
                    <option value="checkout" {{ request('filter') === 'checkout' ? 'selected' : '' }}>From Checkout</option>
                    <option value="manual" {{ request('filter') === 'manual' ? 'selected' : '' }}>Manually Added</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search me-1"></i> Search
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Customer</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Total Orders</th>
                        <th>Total Spent</th>
                        <th>Source</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                                    style="width: 40px; height: 40px;">
                                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $customer->name }}</div>
                                    @if($customer->last_order_at)
                                    <small class="text-muted">Last order: {{ $customer->last_order_at->diffForHumans() }}</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($customer->phone)
                            <a href="tel:{{ $customer->phone }}" class="text-decoration-none">
                                {{ $customer->phone }}
                            </a>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($customer->email)
                            <a href="mailto:{{ $customer->email }}" class="text-decoration-none">
                                {{ $customer->email }}
                            </a>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $customer->total_orders }}</span>
                        </td>
                        <td>
                            <span class="fw-semibold">₹{{ number_format($customer->total_spent, 2) }}</span>
                        </td>
                        <td>
                            @if($customer->is_manually_added)
                            <span class="badge bg-info">Manual</span>
                            @else
                            <span class="badge bg-success">Checkout</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <div class="btn-group">
                                <a href="{{ route('store-owner.customers.show', $customer) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('store-owner.customers.edit', $customer) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('store-owner.customers.destroy', $customer) }}" method="POST" class="d-inline"
                                    onsubmit="return confirm('Are you sure you want to remove this customer?')">
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
                        <td colspan="7" class="text-center py-5">
                            <i class="bi bi-people fs-1 text-muted d-block mb-3"></i>
                            <h5>No customers yet</h5>
                            <p class="text-muted">Customers will appear here when they checkout or you add them manually</p>
                            <a href="{{ route('store-owner.customers.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-lg me-1"></i> Add Customer
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($customers->hasPages())
    <div class="card-footer">
        {{ $customers->links() }}
    </div>
    @endif
</div>
@endsection