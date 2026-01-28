@extends('layouts.app')

@section('title', $store->name)

@push('styles')
<style>
    :root {
        --store-primary: {{ $store->primary_color ?? '#0d6efd' }};
        --store-secondary: {{ $store->secondary_color ?? '#1E293B' }};
        --store-accent: {{ $store->accent_color ?? '#10B981' }};
    }
    
    @if($store->font_family)
    @import url('https://fonts.googleapis.com/css2?family={{ str_replace(' ', '+', $store->font_family) }}:wght@400;500;600;700&display=swap');
    body {
        font-family: '{{ $store->font_family }}', sans-serif;
    }
    @endif

    .store-header {
        background: linear-gradient(135deg, var(--store-primary), color-mix(in srgb, var(--store-primary) 80%, black));
    }
    
    .btn-store-primary {
        background-color: var(--store-primary);
        border-color: var(--store-primary);
        color: white;
    }
    
    .btn-store-primary:hover {
        background-color: color-mix(in srgb, var(--store-primary) 85%, black);
        border-color: color-mix(in srgb, var(--store-primary) 85%, black);
        color: white;
    }

    .text-store-primary {
        color: var(--store-primary);
    }

    .text-store-secondary {
        color: var(--store-secondary);
    }

    .bg-store-primary {
        background-color: var(--store-primary);
    }

    .badge-store {
        background-color: var(--store-primary);
    }

    .list-group-item.active {
        background-color: var(--store-primary);
        border-color: var(--store-primary);
    }

    .card-header {
        background-color: var(--store-primary);
        color: white;
    }

    .product-price {
        color: var(--store-primary);
    }

    .btn-success-custom {
        background-color: var(--store-accent);
        border-color: var(--store-accent);
    }
</style>
@endpush

@section('content')
<!-- Store Header -->
<div class="store-header py-4 mb-4 text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-auto">
                @if($store->logo)
                <img src="{{ asset('storage/' . $store->logo) }}"
                    alt="{{ $store->name }}" class="rounded-circle bg-white p-1"
                    style="width: 80px; height: 80px; object-fit: cover;"
                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <div class="bg-white bg-opacity-25 rounded-circle align-items-center justify-content-center"
                    style="width: 80px; height: 80px; display: none;">
                    <i class="bi bi-shop fs-1"></i>
                </div>
                @else
                <div class="bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center"
                    style="width: 80px; height: 80px;">
                    <i class="bi bi-shop fs-1"></i>
                </div>
                @endif
            </div>
            <div class="col">
                <h1 class="mb-1 text-white">{{ $store->name }}</h1>
                <p class="mb-0 opacity-75">
                    <span class="badge bg-white bg-opacity-25">{{ ucfirst($store->type) }} Store</span>
                    @if($store->address)
                    <i class="bi bi-geo-alt ms-2"></i> {{ $store->address }}
                    @endif
                </p>
                @if($store->description)
                <p class="mt-2 mb-0 opacity-90">{{ $store->description }}</p>
                @endif
            </div>
            <div class="col-auto">
                <a href="{{ route('cart.index', ['store' => $store->slug]) }}" class="btn btn-light position-relative">
                    <i class="bi bi-cart3 me-1"></i> Cart
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-count">
                        {{ $cartCount ?? 0 }}
                    </span>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="row">
        <!-- Categories Sidebar -->
        <div class="col-lg-3 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Categories</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('store.show', $store->slug) }}"
                        class="list-group-item list-group-item-action {{ !request('category') ? 'active' : '' }}">
                        All Products
                    </a>
                    @foreach($categories as $category)
                    <a href="{{ route('store.show', $store->slug) }}?category={{ $category->id }}"
                        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ request('category') == $category->id ? 'active' : '' }}">
                        {{ $category->name }}
                        <span class="badge bg-secondary rounded-pill">{{ $category->products_count }}</span>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="col-lg-9">
            <!-- Search & Sort -->
            <div class="card mb-4">
                <div class="card-body">
                    <form class="row g-3">
                        @if(request('category'))
                        <input type="hidden" name="category" value="{{ request('category') }}">
                        @endif
                        <div class="col-md-8">
                            <input type="search" class="form-control" name="search"
                                placeholder="Search products..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-4">
                            <select class="form-select" name="sort" onchange="this.form.submit()">
                                <option value="">Sort by</option>
                                <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Name (A-Z)</option>
                                <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Name (Z-A)</option>
                                <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Price (Low to High)</option>
                                <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Price (High to Low)</option>
                            </select>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Products -->
            <div class="row g-4">
                @forelse($products as $product)
                <div class="col-6 col-md-4">
                    <div class="card h-100 product-card">
                        @if($product->track_stock && $product->stock_quantity <= 0)
                        <div class="bg-light d-flex align-items-center justify-content-center position-relative"
                            style="height: 200px; cursor: not-allowed;">
                            @if($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}"
                                class="card-img-top" alt="{{ $product->name }}"
                                style="height: 200px; object-fit: cover; opacity: 0.5;">
                            @else
                            <i class="bi bi-box text-muted" style="font-size: 4rem;"></i>
                            @endif
                            <span class="badge bg-secondary position-absolute" style="top: 50%; left: 50%; transform: translate(-50%, -50%);">Out of Stock</span>
                        </div>
                        @else
                        <form action="{{ route('cart.add') }}" method="POST" class="add-to-cart-form h-100 d-flex flex-column">
                            @csrf
                            <input type="hidden" name="store_id" value="{{ $store->id }}">
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <input type="hidden" name="quantity" value="1">
                            <div class="product-image-wrapper position-relative" style="cursor: pointer;" onclick="this.closest('form').requestSubmit();">
                                @if($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}"
                                    class="card-img-top" alt="{{ $product->name }}"
                                    style="height: 200px; object-fit: cover;"
                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="bg-light align-items-center justify-content-center"
                                    style="height: 200px; display: none;">
                                    <i class="bi bi-box text-muted" style="font-size: 4rem;"></i>
                                </div>
                                @else
                                <div class="bg-light d-flex align-items-center justify-content-center"
                                    style="height: 200px;">
                                    <i class="bi bi-box text-muted" style="font-size: 4rem;"></i>
                                </div>
                                @endif
                                <div class="position-absolute top-0 start-0 end-0 bottom-0 d-flex align-items-center justify-content-center hover-overlay" style="background: rgba(0,0,0,0); transition: all 0.2s;">
                                    <span class="btn btn-store-primary btn-sm opacity-0 add-hint">
                                        <i class="bi bi-cart-plus me-1"></i>Add to Cart
                                    </span>
                                </div>
                            </div>
                        @endif
                        @if($product->sale_price)
                        <span class="badge bg-danger position-absolute" style="top: 10px; right: 10px;">
                            Sale
                        </span>
                        @endif
                        <div class="card-body flex-grow-1">
                            <h6 class="card-title mb-2 text-truncate" title="{{ $product->name }}">
                                {{ $product->name }}
                            </h6>
                            <p class="card-text mb-2">
                                @if($product->sale_price)
                                <span class="text-decoration-line-through text-muted">{{ \App\Helpers\CurrencyHelper::format($product->price, $store->currency ?? 'INR') }}</span>
                                <span class="text-danger fw-bold">{{ \App\Helpers\CurrencyHelper::format($product->sale_price, $store->currency ?? 'INR') }}</span>
                                @else
                                <span class="fw-bold product-price">{{ \App\Helpers\CurrencyHelper::format($product->price, $store->currency ?? 'INR') }}</span>
                                @endif
                            </p>
                            @if($product->track_stock && $product->stock_quantity <= 0)
                                <span class="badge bg-secondary">Out of Stock</span>
                                @else
                                <button type="submit" class="btn btn-store-primary btn-sm w-100">
                                    <i class="bi bi-cart-plus me-1"></i>Add to Cart
                                </button>
                            </form>
                                @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-box-seam fs-1 text-muted mb-3 d-block"></i>
                            <h5>No products found</h5>
                            <p class="text-muted">Try adjusting your search or filters</p>
                        </div>
                    </div>
                </div>
                @endforelse
            </div>

            @if($products->hasPages())
            <div class="mt-4">
                {{ $products->appends(request()->query())->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<style>
    .product-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    
    .product-image-wrapper:hover .hover-overlay {
        background: rgba(0,0,0,0.3) !important;
    }
    
    .product-image-wrapper:hover .add-hint {
        opacity: 1 !important;
    }
    
    /* Mobile responsive improvements */
    @media (max-width: 767px) {
        .store-header {
            padding: 15px 0 !important;
        }
        .store-header h1 {
            font-size: 1.25rem;
        }
        .store-header .rounded-circle {
            width: 50px !important;
            height: 50px !important;
        }
        .product-card img, .product-image-wrapper > div {
            height: 140px !important;
        }
        .card-body {
            padding: 10px !important;
        }
        .card-title {
            font-size: 0.9rem;
        }
        .product-price {
            font-size: 0.95rem;
        }
        .col-lg-3 {
            display: none;
        }
    }
</style>

<script>
    document.querySelectorAll('.add-to-cart-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const btn = this.querySelector('button');
            const originalText = btn.innerHTML;

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Adding...';

            fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update cart count
                        document.querySelectorAll('.cart-count').forEach(el => {
                            el.textContent = data.cartCount;
                        });

                        // Show success state
                        btn.innerHTML = '<i class="bi bi-check me-1"></i>Added!';
                        btn.classList.remove('btn-store-primary');
                        btn.classList.add('btn-success-custom');

                        setTimeout(() => {
                            btn.innerHTML = originalText;
                            btn.classList.remove('btn-success-custom');
                            btn.classList.add('btn-store-primary');
                            btn.disabled = false;
                        }, 1500);
                    } else {
                        alert(data.message || 'Failed to add to cart');
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
        });
    });
</script>
@endsection