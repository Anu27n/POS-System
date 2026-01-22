@extends('layouts.admin')

@section('title', 'Plan Features')
@section('page-title', 'Plan Features')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="text-muted mb-0">Manage features that can be assigned to plans</p>
    </div>
    <div>
        <form action="{{ route('admin.plan-features.seed-defaults') }}" method="POST" class="d-inline me-2">
            @csrf
            <button type="submit" class="btn btn-outline-secondary">
                <i class="bi bi-lightning me-1"></i> Seed Default Features
            </button>
        </form>
        <a href="{{ route('admin.plan-features.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Add Feature
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@foreach($categories as $categorySlug => $categoryName)
    @php $categoryFeatures = $groupedFeatures->get($categorySlug, collect()); @endphp
    @if($categoryFeatures->count() > 0)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">{{ $categoryName }}</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Feature Name</th>
                                <th>Slug</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th width="120">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categoryFeatures as $feature)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">
                                            @if($feature->icon)
                                                <i class="bi bi-{{ $feature->icon }} me-2"></i>
                                            @endif
                                            {{ $feature->name }}
                                        </div>
                                    </td>
                                    <td><code>{{ $feature->slug }}</code></td>
                                    <td>
                                        <small class="text-muted">{{ Str::limit($feature->description, 50) }}</small>
                                    </td>
                                    <td>
                                        @if($feature->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.plan-features.edit', $feature) }}" class="btn btn-outline-primary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('admin.plan-features.destroy', $feature) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
@endforeach

@if($features->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-list-check fs-1 text-muted d-block mb-3"></i>
            <h5>No features defined yet</h5>
            <p class="text-muted mb-3">Create features that can be assigned to subscription plans</p>
            <form action="{{ route('admin.plan-features.seed-defaults') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-lightning me-1"></i> Seed Default Features
                </button>
            </form>
        </div>
    </div>
@endif
@endsection
