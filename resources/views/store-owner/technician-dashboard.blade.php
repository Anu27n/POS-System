@extends('layouts.store-owner')

@section('title', 'Technician Dashboard')
@section('page-title', 'My Jobs')

@section('content')
<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="stat-label">My Open Jobs</div>
                <div class="stat-value text-primary">{{ $myOpenJobs }}</div>
                <small class="text-muted">Assigned to me</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card" style="border-left-color: #d97706;">
            <div class="card-body">
                <div class="stat-label">Due Today</div>
                <div class="stat-value">{{ $myDueToday }}</div>
                @if($myOverdue > 0)
                <small class="text-danger"><i class="bi bi-exclamation-triangle"></i> {{ $myOverdue }} overdue</small>
                @else
                <small class="text-muted">On schedule</small>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card" style="border-left-color: #059669;">
            <div class="card-body">
                <div class="stat-label">Completed This Week</div>
                <div class="stat-value text-success">{{ $completedThisWeek }}</div>
                <small class="text-muted">Great work!</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card" style="border-left-color: #7c3aed;">
            <div class="card-body">
                <div class="stat-label">Avg. Repair Time</div>
                <div class="stat-value">{{ $avgRepairTime }}</div>
                <small class="text-muted">Hours per job</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- My Active Jobs -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-tools me-2"></i>My Active Jobs</h6>
                <a href="{{ route('store-owner.repair-jobs.index', ['technician_id' => $technician->id]) }}" class="btn btn-sm btn-outline-primary">
                    View All My Jobs
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Ticket #</th>
                                <th>Device</th>
                                <th>Issue</th>
                                <th>Priority</th>
                                <th>Due</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($myActiveJobs as $job)
                            <tr class="{{ $job->is_overdue ? 'table-danger' : '' }}">
                                <td>
                                    <a href="{{ route('store-owner.repair-jobs.show', $job) }}" class="fw-semibold text-decoration-none">
                                        #{{ $job->ticket_number }}
                                    </a>
                                    <br>
                                    <small class="text-muted">{{ $job->customer->name ?? 'Walk-in' }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ $job->device_type_label }}</span>
                                    <br>
                                    <small>{{ Str::limit($job->device_name, 20) }}</small>
                                </td>
                                <td>
                                    <span title="{{ $job->issue_description }}">
                                        {{ Str::limit($job->issue_description, 40) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $job->priority_color }}">{{ $job->priority_label }}</span>
                                </td>
                                <td>
                                    @if($job->expected_delivery_at)
                                        @if($job->is_overdue)
                                        <span class="text-danger fw-bold">
                                            <i class="bi bi-exclamation-circle"></i>
                                            {{ $job->expected_delivery_at->format('M d') }}
                                        </span>
                                        @else
                                        {{ $job->expected_delivery_at->format('M d, H:i') }}
                                        @endif
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('store-owner.repair-jobs.show', $job) }}" 
                                           class="btn btn-outline-primary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @if($job->status === 'received' || $job->status === 'diagnosed')
                                        <form action="{{ route('store-owner.repair-jobs.update-status', $job) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="in_progress">
                                            <button type="submit" class="btn btn-outline-success" title="Start Repair">
                                                <i class="bi bi-play-fill"></i>
                                            </button>
                                        </form>
                                        @elseif($job->status === 'in_progress')
                                        <form action="{{ route('store-owner.repair-jobs.update-status', $job) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="repaired">
                                            <button type="submit" class="btn btn-outline-success" title="Mark Repaired">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="bi bi-emoji-smile fs-1 d-block mb-2"></i>
                                    No active jobs assigned to you!
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Sidebar -->
    <div class="col-lg-4">
        <!-- Performance Summary -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-graph-up me-2"></i>My Performance</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span>Jobs Completed (This Month)</span>
                    <span class="badge bg-success rounded-pill">{{ $completedThisMonth }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span>Jobs Completed (Today)</span>
                    <span class="badge bg-primary rounded-pill">{{ $completedToday }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span>On-Time Delivery Rate</span>
                    <span class="badge bg-{{ $onTimeRate >= 90 ? 'success' : ($onTimeRate >= 75 ? 'warning' : 'danger') }} rounded-pill">
                        {{ $onTimeRate }}%
                    </span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span>Average Rating</span>
                    <span class="text-warning">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="bi bi-star{{ $i <= round($avgRating) ? '-fill' : '' }}"></i>
                        @endfor
                    </span>
                </div>
            </div>
        </div>

        <!-- Quick Status Update -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-lightning me-2"></i>Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('store-owner.repair-jobs.index', ['status' => 'received', 'technician_id' => $technician->id]) }}" 
                       class="btn btn-outline-secondary">
                        <i class="bi bi-inbox me-2"></i>View Received Jobs
                    </a>
                    <a href="{{ route('store-owner.repair-jobs.index', ['status' => 'in_progress', 'technician_id' => $technician->id]) }}" 
                       class="btn btn-outline-primary">
                        <i class="bi bi-tools me-2"></i>Jobs In Progress
                    </a>
                    <a href="{{ route('store-owner.repair-jobs.index', ['status' => 'repaired', 'technician_id' => $technician->id]) }}" 
                       class="btn btn-outline-success">
                        <i class="bi bi-check-circle me-2"></i>Ready for Pickup
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
