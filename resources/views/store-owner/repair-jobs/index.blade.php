@extends('layouts.store-owner')

@section('title', 'Repair Jobs')
@section('page-title', 'Repair Jobs')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Repair Jobs</h4>
        <p class="text-muted mb-0">Manage all repair tickets and job cards</p>
    </div>
    @if(auth()->user()->isStoreOwner() || auth()->user()->hasStaffPermission('manage_repair_jobs'))
    <a href="{{ route('store-owner.repair-jobs.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> New Repair Job
    </a>
    @endif
</div>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card stat-card h-100" style="border-left-color: #3b82f6;">
            <div class="card-body">
                <div class="stat-value text-primary">{{ $stats['open'] }}</div>
                <div class="stat-label">Open Jobs</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card h-100" style="border-left-color: #f59e0b;">
            <div class="card-body">
                <div class="stat-value text-warning">{{ $stats['due_today'] }}</div>
                <div class="stat-label">Due Today</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card h-100" style="border-left-color: #ef4444;">
            <div class="card-body">
                <div class="stat-value text-danger">{{ $stats['overdue'] }}</div>
                <div class="stat-label">Overdue</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card h-100" style="border-left-color: #10b981;">
            <div class="card-body">
                <div class="stat-value text-success">{{ $stats['completed_today'] }}</div>
                <div class="stat-label">Completed Today</div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search ticket, device, customer..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    @foreach(\App\Models\RepairJob::STATUSES as $key => $status)
                    <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $status['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="technician_id" class="form-select">
                    <option value="">All Technicians</option>
                    @foreach($technicians as $tech)
                    <option value="{{ $tech->id }}" {{ request('technician_id') == $tech->id ? 'selected' : '' }}>{{ $tech->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="priority" class="form-select">
                    <option value="">All Priority</option>
                    @foreach(\App\Models\RepairJob::PRIORITIES as $key => $priority)
                    <option value="{{ $key }}" {{ request('priority') == $key ? 'selected' : '' }}>{{ $priority['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i> Filter
                    </button>
                    <a href="{{ route('store-owner.repair-jobs.index') }}" class="btn btn-outline-secondary">Clear</a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Jobs Table -->
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Ticket #</th>
                    <th>Customer</th>
                    <th>Device</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Technician</th>
                    <th>Due Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($repairJobs as $job)
                <tr class="{{ $job->is_overdue ? 'table-danger' : '' }}">
                    <td>
                        <a href="{{ route('store-owner.repair-jobs.show', $job) }}" class="fw-bold text-decoration-none">
                            {{ $job->ticket_number }}
                        </a>
                        @if($job->is_overdue)
                        <span class="badge bg-danger ms-1">Overdue</span>
                        @endif
                    </td>
                    <td>
                        @if($job->customer)
                        <div>{{ $job->customer->name }}</div>
                        <small class="text-muted">{{ $job->customer->phone }}</small>
                        @else
                        <span class="text-muted">Walk-in</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-{{ $job->device_type == 'phone' ? 'phone' : ($job->device_type == 'laptop' ? 'laptop' : 'device-hdd') }} me-2 text-muted"></i>
                            <div>
                                <div>{{ $job->device_name }}</div>
                                @if($job->imei_serial)
                                <small class="text-muted">{{ Str::limit($job->imei_serial, 15) }}</small>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-{{ $job->status_color }}">{{ $job->status_label }}</span>
                    </td>
                    <td>
                        <span class="badge bg-{{ $job->priority_color }}">{{ $job->priority_label }}</span>
                    </td>
                    <td>
                        @if($job->technician)
                        <span class="badge bg-light text-dark">
                            <i class="bi bi-person me-1"></i>{{ $job->technician->name }}
                        </span>
                        @else
                        <span class="text-muted">Unassigned</span>
                        @endif
                    </td>
                    <td>
                        @if($job->expected_delivery_at)
                        <small>{{ $job->expected_delivery_at->format('M d, Y') }}</small>
                        @else
                        <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        <div class="btn-group">
                            <a href="{{ route('store-owner.repair-jobs.show', $job) }}" class="btn btn-sm btn-outline-primary" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if(auth()->user()->isStoreOwner() || auth()->user()->hasStaffPermission('manage_repair_jobs'))
                            <a href="{{ route('store-owner.repair-jobs.edit', $job) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @endif
                            <a href="{{ route('store-owner.repair-jobs.print', $job) }}" class="btn btn-sm btn-outline-dark" target="_blank" title="Print Job Card">
                                <i class="bi bi-printer"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <i class="bi bi-inbox display-4 text-muted d-block mb-3"></i>
                        <p class="text-muted mb-0">No repair jobs found</p>
                        @if(auth()->user()->isStoreOwner() || auth()->user()->hasStaffPermission('manage_repair_jobs'))
                        <a href="{{ route('store-owner.repair-jobs.create') }}" class="btn btn-primary mt-3">
                            <i class="bi bi-plus-lg me-1"></i> Create First Repair Job
                        </a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($repairJobs->hasPages())
    <div class="card-footer">
        {{ $repairJobs->links() }}
    </div>
    @endif
</div>
@endsection
