@extends('layouts.store-owner')

@section('title', 'Store Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="stat-label">Today's Sales</div>
                <div class="stat-value">₹{{ number_format($todaySales, 2) }}</div>
                <small class="text-muted">{{ $todayOrders }} orders</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card" style="border-left-color: #059669;">
            <div class="card-body">
                <div class="stat-label">This Month</div>
                <div class="stat-value">₹{{ number_format($monthSales, 2) }}</div>
                <small class="text-muted">{{ $monthOrders }} orders</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card" style="border-left-color: #d97706;">
            <div class="card-body">
                <div class="stat-label">Pending Orders</div>
                <div class="stat-value">{{ $pendingOrders }}</div>
                <small class="text-muted">Needs attention</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card" style="border-left-color: #7c3aed;">
            <div class="card-body">
                <div class="stat-label">Products</div>
                <div class="stat-value">{{ $totalProducts }}</div>
                <small class="text-muted">{{ $lowStockProducts }} low stock</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Store QR Code -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Store QR Code</h6>
            </div>
            <div class="card-body text-center">
                @if($store)
                    <div class="mb-3" style="max-width: 200px; margin: 0 auto;">
                        <img src="{{ $qrCode }}" alt="Store QR Code" class="img-fluid">
                    </div>
                    <p class="text-muted mb-3">Scan to view your store</p>
                    <div class="d-grid gap-2">
                        <a href="{{ route('store.show', $store->slug) }}" 
                           target="_blank" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-eye me-1"></i>View Store
                        </a>
                        <button class="btn btn-outline-secondary btn-sm" onclick="printQR()">
                            <i class="bi bi-printer me-1"></i>Print QR
                        </button>
                    </div>
                @else
                    <div class="text-muted py-4">
                        <i class="bi bi-shop fs-1 d-block mb-2"></i>
                        No store assigned
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Low Stock Alert -->
        @if($lowStockItems->count() > 0)
        <div class="card mt-4">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0"><i class="bi bi-exclamation-triangle me-1"></i>Low Stock Alert</h6>
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
    
    <!-- Recent Orders -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Recent Orders</h6>
                <a href="{{ route('store-owner.orders.index') }}" class="btn btn-sm btn-outline-primary">
                    View All
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentOrders as $order)
                            <tr>
                                <td>
                                    <span class="fw-semibold">#{{ $order->order_number }}</span>
                                    <br>
                                    <small class="text-muted">{{ $order->created_at->diffForHumans() }}</small>
                                </td>
                                <td>{{ $order->user->name ?? 'Guest' }}</td>
                                <td>{{ $order->items->count() }}</td>
                                <td>₹{{ number_format($order->total_amount, 2) }}</td>
                                <td>
                                    @switch($order->status)
                                        @case('pending')
                                            <span class="badge bg-warning">Pending</span>
                                            @break
                                        @case('confirmed')
                                            <span class="badge bg-info">Confirmed</span>
                                            @break
                                        @case('processing')
                                            <span class="badge bg-primary">Processing</span>
                                            @break
                                        @case('completed')
                                            <span class="badge bg-success">Completed</span>
                                            @break
                                        @case('cancelled')
                                            <span class="badge bg-danger">Cancelled</span>
                                            @break
                                    @endswitch
                                </td>
                                <td>
                                    <a href="{{ route('store-owner.orders.show', $order) }}" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No orders yet</td>
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
                <a href="{{ route('store-owner.pos.index') }}" class="card text-decoration-none h-100">
                    <div class="card-body text-center py-4">
                        <i class="bi bi-cash-register fs-1 text-primary mb-2 d-block"></i>
                        <h6 class="mb-0">Open POS</h6>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="{{ route('store-owner.products.create') }}" class="card text-decoration-none h-100">
                    <div class="card-body text-center py-4">
                        <i class="bi bi-plus-circle fs-1 text-success mb-2 d-block"></i>
                        <h6 class="mb-0">Add Product</h6>
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

<script>
function printQR() {
    const qrImage = document.querySelector('img[alt="Store QR Code"]');
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Store QR Code</title>
                <style>
                    body { text-align: center; padding: 50px; }
                    img { max-width: 300px; }
                    h2 { margin-bottom: 20px; }
                </style>
            </head>
            <body>
                <h2>{{ $store->name ?? 'Store' }}</h2>
                <img src="${qrImage.src}" alt="QR Code">
                <p>Scan to order</p>
            </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}
</script>
@endsection

