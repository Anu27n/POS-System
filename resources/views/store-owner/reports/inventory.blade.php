@extends('layouts.store-owner')

@section('title', 'Inventory Report')
@section('page-title', 'Inventory Report')

@section('content')
<!-- Summary Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="stat-label">Total Products</div>
                <div class="stat-value">{{ $totalProducts }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card" style="border-left-color: #059669;">
            <div class="card-body">
                <div class="stat-label">Total Stock Value</div>
                <div class="stat-value">₹{{ number_format($totalStockValue, 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card" style="border-left-color: #d97706;">
            <div class="card-body">
                <div class="stat-label">Low Stock Items</div>
                <div class="stat-value text-warning">{{ $lowStockProducts->count() }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card" style="border-left-color: #dc2626;">
            <div class="card-body">
                <div class="stat-label">Out of Stock</div>
                <div class="stat-value text-danger">{{ $outOfStockProducts->count() }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Low Stock Alert -->
    @if($lowStockProducts->count() > 0)
    <div class="col-lg-6">
        <div class="card border-warning">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Low Stock Alert</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th class="text-center">Stock</th>
                                <th class="text-end">Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lowStockProducts as $product)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $product->name }}</div>
                                    @if($product->sku)
                                    <small class="text-muted">SKU: {{ $product->sku }}</small>
                                    @endif
                                </td>
                                <td>{{ $product->category->name ?? 'Uncategorized' }}</td>
                                <td class="text-center">
                                    <span class="badge bg-warning text-dark">{{ $product->stock_quantity }}</span>
                                </td>
                                <td class="text-end">₹{{ number_format($product->price, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Out of Stock -->
    @if($outOfStockProducts->count() > 0)
    <div class="col-lg-6">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h6 class="mb-0"><i class="bi bi-x-circle me-2"></i>Out of Stock</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th class="text-end">Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($outOfStockProducts as $product)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $product->name }}</div>
                                    @if($product->sku)
                                    <small class="text-muted">SKU: {{ $product->sku }}</small>
                                    @endif
                                </td>
                                <td>{{ $product->category->name ?? 'Uncategorized' }}</td>
                                <td class="text-end">₹{{ number_format($product->price, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- All Products Inventory -->
<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">All Products Inventory</h6>
        <span class="badge bg-primary">{{ $totalProducts }} products</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th class="text-center">Stock</th>
                        <th class="text-end">Unit Price</th>
                        <th class="text-end">Stock Value</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $product->name }}</div>
                            @if($product->sku)
                            <small class="text-muted">SKU: {{ $product->sku }}</small>
                            @endif
                        </td>
                        <td>{{ $product->category->name ?? 'Uncategorized' }}</td>
                        <td class="text-center">
                            @if($product->track_inventory)
                            @if($product->stock_quantity === 0)
                            <span class="badge bg-danger">0</span>
                            @elseif($product->isLowStock())
                            <span class="badge bg-warning text-dark">{{ $product->stock_quantity }}</span>
                            @else
                            <span class="badge bg-success">{{ $product->stock_quantity }}</span>
                            @endif
                            @else
                            <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td class="text-end">₹{{ number_format($product->price, 2) }}</td>
                        <td class="text-end">
                            @if($product->track_inventory)
                            ₹{{ number_format($product->price * $product->stock_quantity, 2) }}
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($product->status === 'available')
                            <span class="badge bg-success">Available</span>
                            @else
                            <span class="badge bg-secondary">{{ ucfirst($product->status) }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="bi bi-box text-muted" style="font-size: 2rem;"></i>
                            <p class="text-muted mt-2 mb-0">No products found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection