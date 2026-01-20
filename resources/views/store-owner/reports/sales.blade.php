@extends('layouts.store-owner')

@section('title', 'Sales Report')
@section('page-title', 'Sales Report')

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Start Date</label>
                <input type="date" class="form-control" name="start_date" value="{{ $startDate }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">End Date</label>
                <input type="date" class="form-control" name="end_date" value="{{ $endDate }}">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">Generate Report</button>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="stat-label">Total Sales</div>
                <div class="stat-value">₹{{ number_format($totalSales, 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card" style="border-left-color: #059669;">
            <div class="card-body">
                <div class="stat-label">Total Orders</div>
                <div class="stat-value">{{ $totalOrders }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card" style="border-left-color: #d97706;">
            <div class="card-body">
                <div class="stat-label">Average Order</div>
                <div class="stat-value">₹{{ number_format($averageOrder, 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card" style="border-left-color: #7c3aed;">
            <div class="card-body">
                <div class="stat-label">Products Sold</div>
                <div class="stat-value">{{ $productsSold }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Daily Sales -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Daily Sales</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Orders</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($dailySales as $day)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($day->date)->format('M d, Y (D)') }}</td>
                                <td>{{ $day->orders }}</td>
                                <td>₹{{ number_format($day->total, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-4 text-muted">No sales data for selected period</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Payment Methods -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Payment Methods</h6>
            </div>
            <div class="card-body">
                @forelse($paymentMethods as $method)
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="fw-semibold">{{ ucfirst($method->payment_method) }}</div>
                        <small class="text-muted">{{ $method->count }} orders</small>
                    </div>
                    <span class="badge bg-primary">₹{{ number_format($method->total, 2) }}</span>
                </div>
                @empty
                <p class="text-muted mb-0">No data</p>
                @endforelse
            </div>
        </div>
        
        <!-- Top Products -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Top Selling Products</h6>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($topProducts as $product)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <div>{{ $product->product_name }}</div>
                            <small class="text-muted">{{ $product->quantity }} sold</small>
                        </div>
                        <span class="fw-semibold">₹{{ number_format($product->revenue, 2) }}</span>
                    </li>
                    @empty
                    <li class="list-group-item text-muted">No data</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

