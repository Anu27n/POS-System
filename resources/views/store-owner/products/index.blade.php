@extends('layouts.store-owner')

@section('title', 'Products')
@section('page-title', 'Products')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div class="w-100 w-md-auto">
        <form class="d-flex flex-wrap flex-md-nowrap gap-2">
            <input type="search" class="form-control" name="search" style="min-width: 150px;"
                   placeholder="Search products..." value="{{ request('search') }}">
            <select class="form-select flex-shrink-0" name="category" style="width: auto; min-width: 120px;">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-outline-primary">Filter</button>
        </form>
    </div>
    <div class="d-flex justify-content-end w-100 w-md-auto">
        <a href="{{ route('store-owner.products.create') }}" class="btn btn-primary text-nowrap">
            <i class="bi bi-plus-lg me-1"></i>Add Product
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th width="80">Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr>
                        <td>
                            @if($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" 
                                     alt="{{ $product->name }}" class="rounded" 
                                     style="width: 60px; height: 60px; object-fit: cover;"
                                     onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2260%22 height=%2260%22%3E%3Crect fill=%22%23f3f4f6%22 width=%2260%22 height=%2260%22/%3E%3Ctext fill=%22%239ca3af%22 font-family=%22sans-serif%22 font-size=%2224%22 dy=%2210.5%22 x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22%3E📦%3C/text%3E%3C/svg%3E';">
                            @else
                                <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                     style="width: 50px; height: 50px;">
                                    <i class="bi bi-box text-muted"></i>
                                </div>
                            @endif
                        </td>
                        <td>
                            <span class="fw-semibold">{{ $product->name }}</span>
                            @if($product->sku)
                                <br><small class="text-muted">SKU: {{ $product->sku }}</small>
                            @endif
                        </td>
                        <td>{{ $product->category->name ?? 'Uncategorized' }}</td>
                        <td>
                            @if($product->sale_price)
                                <span class="text-decoration-line-through text-muted">₹{{ number_format($product->price, 2) }}</span>
                                <br>
                                <span class="text-success fw-semibold">₹{{ number_format($product->sale_price, 2) }}</span>
                            @else
                                <span class="fw-semibold">₹{{ number_format($product->price, 2) }}</span>
                            @endif
                        </td>
                        <td>
                            @if(!$product->track_inventory)
                                <span class="badge bg-secondary">Not Tracked</span>
                            @elseif($product->stock_quantity <= 0)
                                <span class="badge bg-danger">Out of Stock</span>
                            @elseif($product->stock_quantity <= $product->low_stock_threshold)
                                <span class="badge bg-warning">{{ $product->stock_quantity }}</span>
                            @else
                                <span class="badge bg-success">{{ $product->stock_quantity }}</span>
                            @endif
                        </td>
                        <td>
                            @if($product->status === 'available')
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('store-owner.products.edit', $product) }}" 
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('store-owner.products.destroy', $product) }}" 
                                  method="POST" class="d-inline"
                                  onsubmit="return confirm('Are you sure you want to delete this product?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <div class="text-muted">
                                <i class="bi bi-box fs-1 d-block mb-2"></i>
                                No products yet
                            </div>
                            <a href="{{ route('store-owner.products.create') }}" class="btn btn-primary btn-sm mt-2">
                                Add your first product
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($products->hasPages())
    <div class="card-footer">
        {{ $products->appends(request()->query())->links() }}
    </div>
    @endif
</div>
@endsection

