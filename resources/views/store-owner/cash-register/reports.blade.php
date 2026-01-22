@extends('layouts.store-owner')

@section('title', 'Cash Register Reports')
@section('page-title', 'Cash Register Reports')

@section('content')
<div class="mb-4">
    <a href="{{ route('store-owner.cash-register.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Cash Register
    </a>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('store-owner.cash-register.reports') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Period</label>
                <select class="form-select" name="period" id="periodSelect">
                    <option value="today" {{ $period === 'today' ? 'selected' : '' }}>Today</option>
                    <option value="yesterday" {{ $period === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                    <option value="week" {{ $period === 'week' ? 'selected' : '' }}>This Week</option>
                    <option value="month" {{ $period === 'month' ? 'selected' : '' }}>This Month</option>
                    <option value="custom" {{ $period === 'custom' ? 'selected' : '' }}>Custom Range</option>
                </select>
            </div>
            <div class="col-md-3 custom-date" style="{{ $period !== 'custom' ? 'display:none' : '' }}">
                <label class="form-label">Start Date</label>
                <input type="date" class="form-control" name="start_date" value="{{ request('start_date') }}">
            </div>
            <div class="col-md-3 custom-date" style="{{ $period !== 'custom' ? 'display:none' : '' }}">
                <label class="form-label">End Date</label>
                <input type="date" class="form-control" name="end_date" value="{{ request('end_date') }}">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-funnel me-1"></i> Apply
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0 opacity-75">Total Sessions</h6>
                        <h3 class="mb-0">{{ $stats['total_sessions'] }}</h3>
                    </div>
                    <i class="bi bi-calendar-check fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0 opacity-75">Cash Sales</h6>
                        <h3 class="mb-0">{{ \App\Helpers\CurrencyHelper::format($stats['total_cash_sales']) }}</h3>
                    </div>
                    <i class="bi bi-cash-stack fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0 opacity-75">Card Sales</h6>
                        <h3 class="mb-0">{{ \App\Helpers\CurrencyHelper::format($stats['total_card_sales']) }}</h3>
                    </div>
                    <i class="bi bi-credit-card fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card {{ $stats['total_difference'] >= 0 ? 'bg-success' : 'bg-danger' }} text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0 opacity-75">Cash Difference</h6>
                        <h3 class="mb-0">{{ \App\Helpers\CurrencyHelper::format($stats['total_difference']) }}</h3>
                    </div>
                    <i class="bi bi-{{ $stats['total_difference'] >= 0 ? 'check-circle' : 'exclamation-circle' }} fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sessions Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-table me-2"></i>Session Details</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Staff</th>
                        <th>Duration</th>
                        <th class="text-end">Opening</th>
                        <th class="text-end">Cash Sales</th>
                        <th class="text-end">Card Sales</th>
                        <th class="text-end">Expected</th>
                        <th class="text-end">Actual</th>
                        <th class="text-center">Difference</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sessions as $session)
                        <tr>
                            <td>
                                <strong>{{ $session->opened_at->format('d M Y') }}</strong>
                                <small class="text-muted d-block">{{ $session->opened_at->format('h:i A') }}</small>
                            </td>
                            <td>{{ $session->staff?->name ?? $session->user?->name ?? 'N/A' }}</td>
                            <td>
                                @if($session->closed_at)
                                    {{ $session->opened_at->diffForHumans($session->closed_at, true) }}
                                @else
                                    <span class="badge bg-success">Active</span>
                                @endif
                            </td>
                            <td class="text-end">{{ \App\Helpers\CurrencyHelper::format($session->opening_cash) }}</td>
                            <td class="text-end text-success">{{ \App\Helpers\CurrencyHelper::format($session->total_cash_sales) }}</td>
                            <td class="text-end text-info">{{ \App\Helpers\CurrencyHelper::format($session->total_card_sales) }}</td>
                            <td class="text-end">{{ \App\Helpers\CurrencyHelper::format($session->expected_cash) }}</td>
                            <td class="text-end">
                                @if($session->closed_at)
                                    {{ \App\Helpers\CurrencyHelper::format($session->closing_cash) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center">
                                @if($session->closed_at)
                                    @php $diff = $session->closing_cash - $session->expected_cash; @endphp
                                    @if($diff == 0)
                                        <span class="badge bg-success">Balanced</span>
                                    @elseif($diff > 0)
                                        <span class="badge bg-info">+{{ \App\Helpers\CurrencyHelper::format($diff) }}</span>
                                    @else
                                        <span class="badge bg-danger">{{ \App\Helpers\CurrencyHelper::format($diff) }}</span>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('store-owner.cash-register.show', $session) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
                                No sessions found for the selected period
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($sessions->hasPages())
        <div class="card-footer">
            {{ $sessions->appends(request()->query())->links() }}
        </div>
    @endif
</div>

@push('scripts')
<script>
document.getElementById('periodSelect').addEventListener('change', function() {
    const customFields = document.querySelectorAll('.custom-date');
    customFields.forEach(field => {
        field.style.display = this.value === 'custom' ? 'block' : 'none';
    });
});
</script>
@endpush
@endsection
