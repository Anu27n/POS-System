@extends('layouts.store-owner')

@section('title', 'Tax Reports')
@section('page-title', 'Tax Reports')

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route('store-owner.reports.tax') }}" method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Quick Filter</label>
                        <select class="form-select" name="period" id="periodSelect">
                            <option value="today" {{ request('period') === 'today' ? 'selected' : '' }}>Today</option>
                            <option value="yesterday" {{ request('period') === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                            <option value="week" {{ request('period') === 'week' ? 'selected' : '' }}>This Week</option>
                            <option value="month" {{ request('period', 'month') === 'month' ? 'selected' : '' }}>This Month</option>
                            <option value="quarter" {{ request('period') === 'quarter' ? 'selected' : '' }}>This Quarter</option>
                            <option value="year" {{ request('period') === 'year' ? 'selected' : '' }}>This Year</option>
                            <option value="custom" {{ request('period') === 'custom' ? 'selected' : '' }}>Custom Range</option>
                        </select>
                    </div>
                    <div class="col-md-3 custom-date-field" style="{{ request('period') !== 'custom' ? 'display:none' : '' }}">
                        <label class="form-label">Start Date</label>
                        <input type="date" class="form-control" name="start_date" value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-3 custom-date-field" style="{{ request('period') !== 'custom' ? 'display:none' : '' }}">
                        <label class="form-label">End Date</label>
                        <input type="date" class="form-control" name="end_date" value="{{ request('end_date') }}">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-funnel me-1"></i> Apply Filter
                        </button>
                        <a href="{{ route('store-owner.reports.tax.export') }}?{{ http_build_query(request()->query()) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-download me-1"></i> Export CSV
                        </a>
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
                                <h6 class="mb-0 opacity-75">Total Sales</h6>
                                <h3 class="mb-0">{{ \App\Helpers\CurrencyHelper::format($summary['total_sales']) }}</h3>
                            </div>
                            <div class="fs-1 opacity-50">
                                <i class="bi bi-cash-stack"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0 opacity-75">Total Tax Collected</h6>
                                <h3 class="mb-0">{{ \App\Helpers\CurrencyHelper::format($summary['total_tax']) }}</h3>
                            </div>
                            <div class="fs-1 opacity-50">
                                <i class="bi bi-percent"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0 opacity-75">Orders with Tax</h6>
                                <h3 class="mb-0">{{ $summary['order_count'] }}</h3>
                            </div>
                            <div class="fs-1 opacity-50">
                                <i class="bi bi-receipt"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0 opacity-75">Effective Tax Rate</h6>
                                <h3 class="mb-0">{{ $summary['effective_rate'] }}%</h3>
                            </div>
                            <div class="fs-1 opacity-50">
                                <i class="bi bi-graph-up"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tax Breakdown by Type -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-pie-chart me-2"></i>Tax Breakdown by Type</h5>
                    </div>
                    <div class="card-body">
                        @if(count($taxBreakdown) > 0)
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Tax Name</th>
                                            <th class="text-end">Amount</th>
                                            <th class="text-end">%</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($taxBreakdown as $tax)
                                            <tr>
                                                <td>
                                                    <span class="badge bg-secondary me-2">{{ $tax->percentage }}%</span>
                                                    {{ $tax->tax_name }}
                                                </td>
                                                <td class="text-end fw-semibold">{{ \App\Helpers\CurrencyHelper::format($tax->total_amount) }}</td>
                                                <td class="text-end text-muted">
                                                    {{ $summary['total_tax'] > 0 ? number_format(($tax->total_amount / $summary['total_tax']) * 100, 1) : 0 }}%
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <th>Total</th>
                                            <th class="text-end">{{ \App\Helpers\CurrencyHelper::format($summary['total_tax']) }}</th>
                                            <th class="text-end">100%</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-pie-chart fs-1 d-block mb-2"></i>
                                No tax data for selected period
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-calendar3 me-2"></i>Report Period</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Date Range:</span>
                                <strong>{{ $dateRange['start'] }} - {{ $dateRange['end'] }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Days Covered:</span>
                                <strong>{{ $dateRange['days'] }} days</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Avg. Daily Tax:</span>
                                <strong>{{ \App\Helpers\CurrencyHelper::format($dateRange['days'] > 0 ? $summary['total_tax'] / $dateRange['days'] : 0) }}</strong>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="d-grid gap-2">
                            <a href="{{ route('store-owner.tax-settings.index') }}" class="btn btn-outline-primary">
                                <i class="bi bi-gear me-1"></i> Tax Settings
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Tax Transactions -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Tax Transactions</h5>
                <span class="badge bg-secondary">{{ count($taxTransactions) }} transactions</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Order #</th>
                                <th>Tax Name</th>
                                <th class="text-end">Order Value</th>
                                <th class="text-end">Tax Rate</th>
                                <th class="text-end">Tax Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($taxTransactions as $transaction)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($transaction->created_at)->format('d M Y, H:i') }}</td>
                                    <td>
                                        <a href="{{ route('store-owner.orders.show', $transaction->order_id) }}" class="text-decoration-none">
                                            #{{ $transaction->order_id }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ $transaction->tax_name }}</span>
                                    </td>
                                    <td class="text-end">{{ \App\Helpers\CurrencyHelper::format($transaction->order?->subtotal ?? 0) }}</td>
                                    <td class="text-end">{{ $transaction->tax_percentage }}%</td>
                                    <td class="text-end fw-semibold text-success">{{ \App\Helpers\CurrencyHelper::format($transaction->tax_amount) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-receipt fs-1 d-block mb-2"></i>
                                        No tax transactions found for the selected period
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if(method_exists($taxTransactions, 'links'))
                <div class="card-footer">
                    {{ $taxTransactions->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('periodSelect').addEventListener('change', function() {
    const customFields = document.querySelectorAll('.custom-date-field');
    if (this.value === 'custom') {
        customFields.forEach(field => field.style.display = 'block');
    } else {
        customFields.forEach(field => field.style.display = 'none');
    }
});
</script>
@endpush
@endsection
