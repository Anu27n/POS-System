@extends('layouts.store-owner')

@section('title', 'Repair Shop Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<!-- Repair Job Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="stat-label">Open Jobs</div>
                <div class="stat-value text-primary">{{ $openJobs }}</div>
                <small class="text-muted">Active repairs</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card" style="border-left-color: #d97706;">
            <div class="card-body">
                <div class="stat-label">Due Today</div>
                <div class="stat-value">{{ $jobsDueToday }}</div>
                @if($overdueJobs > 0)
                <small class="text-danger"><i class="bi bi-exclamation-triangle"></i> {{ $overdueJobs }} overdue</small>
                @else
                <small class="text-muted">On schedule</small>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card" style="border-left-color: #059669;">
            <div class="card-body">
                <div class="stat-label">Completed Today</div>
                <div class="stat-value text-success">{{ $completedToday }}</div>
                <small class="text-muted">₹{{ number_format($todayRepairRevenue, 0) }} revenue</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card" style="border-left-color: #7c3aed;">
            <div class="card-body">
                <div class="stat-label">This Month Revenue</div>
                <div class="stat-value">₹{{ number_format($monthRepairRevenue, 0) }}</div>
                <small class="text-muted">From repairs</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column -->
    <div class="col-lg-4">
        <!-- Technician Workload -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-people me-2"></i>Technician Workload</h6>
            </div>
            <div class="card-body p-0">
                @if($technicians->count() > 0)
                <ul class="list-group list-group-flush">
                    @foreach($technicians as $tech)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <span class="fw-medium">{{ $tech->name }}</span>
                            <br>
                            <small class="text-muted">{{ $tech->phone ?? 'No phone' }}</small>
                        </div>
                        <span class="badge bg-{{ $tech->active_jobs > 5 ? 'danger' : ($tech->active_jobs > 2 ? 'warning' : 'success') }} rounded-pill">
                            {{ $tech->active_jobs }} jobs
                        </span>
                    </li>
                    @endforeach
                </ul>
                @else
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-person-plus fs-1 d-block mb-2"></i>
                    No technicians assigned
                    <a href="{{ route('store-owner.staff.create') }}" class="d-block mt-2">Add Technician</a>
                </div>
                @endif
            </div>
        </div>

        <!-- Jobs by Status -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-pie-chart me-2"></i>Jobs by Status</h6>
            </div>
            <div class="card-body">
                @php
                    $statuses = [
                        'received' => ['label' => 'Received', 'color' => 'secondary'],
                        'in_progress' => ['label' => 'In Progress', 'color' => 'primary'],
                        'repaired' => ['label' => 'Repaired', 'color' => 'info'],
                        'delivered' => ['label' => 'Delivered', 'color' => 'success'],
                        'cancelled' => ['label' => 'Cancelled', 'color' => 'danger'],
                    ];
                @endphp
                @foreach($statuses as $key => $status)
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-{{ $status['color'] }}">{{ $status['label'] }}</span>
                    <span class="fw-bold">{{ $jobsByStatus[$key] ?? 0 }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Low Stock Alert -->
        @if($lowStockItems->count() > 0)
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0"><i class="bi bi-exclamation-triangle me-1"></i>Low Stock Parts</h6>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @foreach($lowStockItems as $product)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>{{ $product->name }}</span>
                        <span class="badge bg-danger">{{ $product->stock_quantity }} left</span>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif
    </div>

    <!-- Right Column - Recent Jobs -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-tools me-2"></i>Recent Repair Jobs</h6>
                <a href="{{ route('store-owner.repair-jobs.index') }}" class="btn btn-sm btn-outline-primary">
                    View All
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Ticket #</th>
                                <th>Customer</th>
                                <th>Device</th>
                                <th>Technician</th>
                                <th>Status</th>
                                <th>Due</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentJobs as $job)
                            <tr>
                                <td>
                                    <span class="fw-semibold">#{{ $job->ticket_number }}</span>
                                    <br>
                                    <small class="text-muted">{{ $job->created_at->diffForHumans() }}</small>
                                </td>
                                <td>{{ $job->customer->name ?? $job->customer_name ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ ucfirst($job->device_type) }}</span>
                                    <br>
                                    <small>{{ Str::limit($job->device_model, 15) }}</small>
                                </td>
                                <td>{{ $job->technician->name ?? 'Unassigned' }}</td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'received' => 'secondary',
                                            'in_progress' => 'primary',
                                            'repaired' => 'info',
                                            'delivered' => 'success',
                                            'cancelled' => 'danger',
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $statusColors[$job->status] ?? 'secondary' }}">
                                        {{ ucwords(str_replace('_', ' ', $job->status)) }}
                                    </span>
                                </td>
                                <td>
                                    @if($job->expected_delivery_at)
                                        @if($job->expected_delivery_at->isPast() && !in_array($job->status, ['delivered', 'cancelled']))
                                        <span class="text-danger">
                                            <i class="bi bi-exclamation-circle"></i>
                                            {{ $job->expected_delivery_at->format('M d') }}
                                        </span>
                                        @else
                                        {{ $job->expected_delivery_at->format('M d') }}
                                        @endif
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('store-owner.repair-jobs.show', $job) }}"
                                        class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="bi bi-tools fs-1 d-block mb-2"></i>
                                    No repair jobs yet
                                    <br>
                                    <a href="{{ route('store-owner.repair-jobs.create') }}" class="btn btn-primary mt-2">
                                        <i class="bi bi-plus-lg me-1"></i>Create First Job
                                    </a>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row g-3 mt-3">
            <div class="col-md-4">
                <a href="{{ route('store-owner.repair-jobs.create') }}" class="card text-decoration-none h-100">
                    <div class="card-body text-center py-4">
                        <i class="bi bi-plus-circle fs-1 text-primary mb-2 d-block"></i>
                        <h6 class="mb-0">New Repair Job</h6>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="{{ route('store-owner.products.create') }}" class="card text-decoration-none h-100">
                    <div class="card-body text-center py-4">
                        <i class="bi bi-box-seam fs-1 text-success mb-2 d-block"></i>
                        <h6 class="mb-0">Add Spare Part</h6>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="{{ route('store-owner.reports.sales') }}" class="card text-decoration-none h-100">
                    <div class="card-body text-center py-4">
                        <i class="bi bi-graph-up fs-1 text-info mb-2 d-block"></i>
                        <h6 class="mb-0">View Reports</h6>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection