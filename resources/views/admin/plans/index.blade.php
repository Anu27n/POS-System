@extends('layouts.admin')

@section('title', 'Plans')
@section('page-title', 'Subscription Plans')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="text-muted mb-0">Manage subscription plans for store owners</p>
    </div>
    <a href="{{ route('admin.plans.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Create Plan
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Plan Name</th>
                        <th>Price</th>
                        <th>Billing Cycle</th>
                        <th>Features</th>
                        <th>Subscriptions</th>
                        <th>Status</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($plans as $plan)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $plan->name }}</div>
                                @if($plan->is_popular)
                                    <span class="badge bg-warning text-dark">Popular</span>
                                @endif
                            </td>
                            <td>
                                <span class="fw-semibold">₹{{ number_format($plan->price, 2) }}</span>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ ucfirst($plan->billing_cycle) }}</span>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ count($plan->features ?? []) }} features</span>
                            </td>
                            <td>
                                <span class="badge bg-primary">{{ $plan->subscriptions_count ?? $plan->subscriptions()->count() }}</span>
                            </td>
                            <td>
                                @if($plan->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.plans.edit', $plan) }}" class="btn btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.plans.toggle-status', $plan) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-{{ $plan->is_active ? 'warning' : 'success' }}" title="{{ $plan->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="bi bi-{{ $plan->is_active ? 'pause' : 'play' }}"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.plans.destroy', $plan) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this plan?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="bi bi-credit-card-2-front fs-1 d-block mb-2"></i>
                                No plans found. Create your first plan to get started.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-4">
    {{ $plans->links() }}
</div>
@endsection
