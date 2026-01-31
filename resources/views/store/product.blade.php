@extends('layouts.app')

@section('title', $product->name . ' - ' . $store->name)

@section('content')
<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('store.show', $store->slug) }}">{{ $store->name }}</a></li>
            @if($product->category)
                <li class="breadcrumb-item">
                    <a href="{{ route('store.show', ['store' => $store->slug, 'category' => $product->category_id]) }}">
                        {{ $product->category->name }}
                    </a>
                </li>
            @endif
            <li class="breadcrumb-item active">{{ $product->name }}</li>
        </ol>
    </nav>
    
    <div class="row">
        <!-- Product Image -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                @if($product->image)
                    <img src="{{ asset('storage/' . $product->image) }}" 
                         class="card-img-top" alt="{{ $product->name }}"
                         style="max-height: 500px; object-fit: contain;"
                         onerror="this.onerror=null; this.style.display='none'; this.parentElement.querySelector('.fallback-image').style.display='flex';">
                    <div class="bg-light align-items-center justify-content-center fallback-image" 
                         style="height: 400px; display: none;">
                        <i class="bi bi-box text-muted" style="font-size: 8rem;"></i>
                    </div>
                @else
                    <div class="bg-light d-flex align-items-center justify-content-center" 
                         style="height: 400px;">
                        <i class="bi bi-box text-muted" style="font-size: 8rem;"></i>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Product Info -->
        <div class="col-lg-6">
            <h1 class="mb-3">{{ $product->name }}</h1>
            
            @if($product->category)
                <span class="badge bg-secondary mb-3">{{ $product->category->name }}</span>
            @endif
            
            <div class="mb-4">
                @if($product->sale_price)
                    <span class="text-decoration-line-through text-muted fs-4">₹{{ number_format($product->price, 2) }}</span>
                    <span class="text-danger fs-2 fw-bold ms-2">₹{{ number_format($product->sale_price, 2) }}</span>
                    <span class="badge bg-danger ms-2">
                        {{ round((($product->price - $product->sale_price) / $product->price) * 100) }}% OFF
                    </span>
                @else
                    <span class="fs-2 fw-bold">₹{{ number_format($product->price, 2) }}</span>
                @endif
            </div>
            
            @if($product->description)
                <p class="text-muted mb-4">{{ $product->description }}</p>
            @endif
            
            <!-- Stock Status -->
            <div class="mb-4">
                @if($product->track_inventory)
                    @if($product->stock_quantity > 0)
                        <span class="text-success"><i class="bi bi-check-circle me-1"></i>In Stock ({{ $product->stock_quantity }} available)</span>
                    @else
                        <span class="badge bg-danger fs-6"><i class="bi bi-x-circle me-1"></i>Out of Stock</span>
                    @endif
                @else
                    <span class="text-success"><i class="bi bi-check-circle me-1"></i>Available</span>
                @endif
            </div>
            
            <!-- Add to Cart Form -->
            @if(!$product->track_inventory || $product->stock_quantity > 0)
                <form action="{{ route('cart.add') }}" method="POST" id="addToCartForm">
                    @csrf
                    <input type="hidden" name="store_id" value="{{ $store->id }}">
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    
                    <!-- Size Options -->
                    @if($product->sizes && count($product->sizes) > 0)
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Size</label>
                            <div class="btn-group" role="group">
                                @foreach($product->sizes as $size)
                                    <input type="radio" class="btn-check" name="options[size]" 
                                           id="size{{ $loop->index }}" value="{{ $size }}"
                                           {{ $loop->first ? 'checked' : '' }}>
                                    <label class="btn btn-outline-primary" for="size{{ $loop->index }}">
                                        {{ $size }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    <!-- Color Options -->
                    @if($product->colors && count($product->colors) > 0)
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Color</label>
                            <div class="btn-group" role="group">
                                @foreach($product->colors as $color)
                                    <input type="radio" class="btn-check" name="options[color]" 
                                           id="color{{ $loop->index }}" value="{{ $color }}"
                                           {{ $loop->first ? 'checked' : '' }}>
                                    <label class="btn btn-outline-primary" for="color{{ $loop->index }}">
                                        {{ $color }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    <!-- Quantity -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Quantity</label>
                        <div class="input-group" style="max-width: 150px;">
                            <button type="button" class="btn btn-outline-secondary" onclick="changeQty(-1)">-</button>
                            <input type="number" class="form-control text-center" name="quantity" 
                                   id="quantity" value="1" min="1" 
                                   max="{{ $product->track_inventory ? $product->stock_quantity : 999 }}">
                            <button type="button" class="btn btn-outline-secondary" onclick="changeQty(1)">+</button>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="d-grid gap-2 d-md-flex">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-cart-plus me-2"></i>Add to Cart
                        </button>
                        <a href="{{ route('store.show', $store->slug) }}" class="btn btn-outline-secondary btn-lg">
                            Continue Shopping
                        </a>
                    </div>
                </form>
            @else
                <div class="alert alert-danger d-flex align-items-center">
                    <i class="bi bi-x-circle-fill fs-4 me-3"></i>
                    <div>
                        <h5 class="alert-heading mb-1">Out of Stock</h5>
                        <p class="mb-0">This product is currently unavailable. Please check back later.</p>
                    </div>
                </div>
                <a href="{{ route('store.show', $store->slug) }}" class="btn btn-outline-primary mt-2">
                    <i class="bi bi-arrow-left me-2"></i>Continue Shopping
                </a>
            @endif
            
            <!-- Product Details -->
            @if($product->sku || $product->unit || $product->weight)
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="mb-0">Product Details</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm mb-0">
                            @if($product->sku)
                                <tr>
                                    <td class="text-muted">SKU</td>
                                    <td>{{ $product->sku }}</td>
                                </tr>
                            @endif
                            @if($product->unit)
                                <tr>
                                    <td class="text-muted">Unit</td>
                                    <td>{{ $product->unit }}</td>
                                </tr>
                            @endif
                            @if($product->weight)
                                <tr>
                                    <td class="text-muted">Weight</td>
                                    <td>{{ $product->weight }} kg</td>
                                </tr>
                            @endif
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function changeQty(delta) {
    const input = document.getElementById('quantity');
    let value = parseInt(input.value) + delta;
    if (value < 1) value = 1;
    if (input.max && value > parseInt(input.max)) value = parseInt(input.max);
    input.value = value;
}
</script>
@endsection

