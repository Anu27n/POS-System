@extends('layouts.store-owner')

@section('title', 'POS Terminal')
@section('page-title', 'Point of Sale')

@push('styles')
<style>
    .pos-container {
        height: calc(100vh - 140px);
    }
    .products-grid {
        height: 100%;
        overflow-y: auto;
    }
    .product-card {
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
        height: 100%;
    }
    .product-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .product-card img {
        height: 120px;
        object-fit: cover;
    }
    .cart-section {
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .cart-items {
        flex: 1;
        overflow-y: auto;
    }
    .cart-item {
        padding: 10px;
        border-bottom: 1px solid #eee;
    }
    .cart-item:last-child {
        border-bottom: none;
    }
    .cart-summary {
        background: #f8f9fa;
        padding: 15px;
        border-top: 2px solid #dee2e6;
    }
    .qty-btn {
        width: 30px;
        height: 30px;
        padding: 0;
        line-height: 1;
    }
    .category-pills {
        overflow-x: auto;
        white-space: nowrap;
        padding-bottom: 10px;
    }
    .category-pills::-webkit-scrollbar {
        height: 4px;
    }
</style>
@endpush

@section('content')
<div class="row pos-container g-3">
    <!-- Products Section -->
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header">
                <!-- Search & Categories -->
                <div class="mb-3">
                    <input type="text" class="form-control" id="searchProducts" 
                           placeholder="Search products by name, SKU or barcode...">
                </div>
                <div class="category-pills d-flex gap-2">
                    <button class="btn btn-primary btn-sm category-filter active" data-category="all">
                        All
                    </button>
                    @foreach($categories as $category)
                    <button class="btn btn-outline-primary btn-sm category-filter" data-category="{{ $category->id }}">
                        {{ $category->name }}
                    </button>
                    @endforeach
                </div>
            </div>
            <div class="card-body products-grid">
                <div class="row g-3" id="productsGrid">
                    @foreach($products as $product)
                    <div class="col-6 col-md-4 col-xl-3 product-item" 
                         data-category="{{ $product->category_id }}"
                         data-name="{{ strtolower($product->name) }}"
                         data-sku="{{ strtolower($product->sku ?? '') }}"
                         data-barcode="{{ strtolower($product->barcode ?? '') }}">
                        <div class="card product-card" 
                             onclick="addToCart({{ json_encode([
                                 'id' => $product->id,
                                 'name' => $product->name,
                                 'price' => $product->sale_price ?? $product->price,
                                 'image' => $product->image ? asset('storage/' . $product->image) : null,
                                 'stock' => $product->stock_quantity,
                                 'track_stock' => $product->track_stock,
                                 'sizes' => $product->sizes,
                                 'colors' => $product->colors
                             ]) }})">
                            @if($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" class="card-img-top" alt="{{ $product->name }}">
                            @else
                                <div class="bg-light d-flex align-items-center justify-content-center" style="height: 120px;">
                                    <i class="bi bi-box text-muted fs-1"></i>
                                </div>
                            @endif
                            <div class="card-body p-2 text-center">
                                <h6 class="card-title mb-1 text-truncate" title="{{ $product->name }}">
                                    {{ $product->name }}
                                </h6>
                                <p class="card-text mb-0 fw-bold text-primary">
                                    ${{ number_format($product->sale_price ?? $product->price, 2) }}
                                </p>
                                @if($product->track_stock)
                                    <small class="text-muted">Stock: {{ $product->stock_quantity }}</small>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    
    <!-- Cart Section -->
    <div class="col-lg-4">
        <div class="card h-100 cart-section">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-cart3 me-2"></i>Current Order</h5>
                <button class="btn btn-sm btn-outline-danger" onclick="clearCart()">
                    <i class="bi bi-trash"></i> Clear
                </button>
            </div>
            
            <div class="cart-items" id="cartItems">
                <div class="text-center text-muted py-5" id="emptyCart">
                    <i class="bi bi-cart fs-1 mb-2 d-block"></i>
                    <p>Cart is empty</p>
                    <small>Click on products to add them</small>
                </div>
            </div>
            
            <div class="cart-summary">
                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal:</span>
                    <span id="cartSubtotal">$0.00</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Tax (<span id="taxRate">{{ $taxRate ?? 0 }}</span>%):</span>
                    <span id="cartTax">$0.00</span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span>Discount:</span>
                    <div class="input-group input-group-sm" style="width: 100px;">
                        <span class="input-group-text">$</span>
                        <input type="number" class="form-control" id="discountAmount" value="0" min="0" step="0.01">
                    </div>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-3">
                    <strong class="fs-5">Total:</strong>
                    <strong class="fs-5" id="cartTotal">$0.00</strong>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Payment Method</label>
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="paymentMethod" id="payCash" value="cash" checked>
                        <label class="btn btn-outline-success" for="payCash">
                            <i class="bi bi-cash"></i> Cash
                        </label>
                        <input type="radio" class="btn-check" name="paymentMethod" id="payCard" value="card">
                        <label class="btn btn-outline-primary" for="payCard">
                            <i class="bi bi-credit-card"></i> Card
                        </label>
                    </div>
                </div>
                
                <div class="d-grid gap-2">
                    <button class="btn btn-success btn-lg" onclick="processOrder()" id="checkoutBtn" disabled>
                        <i class="bi bi-check-circle me-1"></i>Complete Order
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Options Modal -->
<div class="modal fade" id="optionsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select Options</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="sizeOptions" class="mb-3" style="display: none;">
                    <label class="form-label">Size</label>
                    <div class="btn-group w-100" role="group" id="sizeButtons"></div>
                </div>
                <div id="colorOptions" class="mb-3" style="display: none;">
                    <label class="form-label">Color</label>
                    <div class="btn-group w-100" role="group" id="colorButtons"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Quantity</label>
                    <input type="number" class="form-control" id="optionQty" value="1" min="1">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="confirmAddToCart()">Add to Cart</button>
            </div>
        </div>
    </div>
</div>

<script>
let cart = [];
let pendingProduct = null;
const taxRate = {{ $taxRate ?? 0 }};

// Category Filter
document.querySelectorAll('.category-filter').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.category-filter').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        const category = this.dataset.category;
        document.querySelectorAll('.product-item').forEach(item => {
            if (category === 'all' || item.dataset.category == category) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    });
});

// Search
document.getElementById('searchProducts').addEventListener('input', function() {
    const search = this.value.toLowerCase();
    document.querySelectorAll('.product-item').forEach(item => {
        const name = item.dataset.name;
        const sku = item.dataset.sku;
        const barcode = item.dataset.barcode;
        if (name.includes(search) || sku.includes(search) || barcode.includes(search)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
});

// Add to Cart
function addToCart(product) {
    if (product.sizes || product.colors) {
        pendingProduct = product;
        showOptionsModal(product);
    } else {
        addProductToCart(product, 1, {});
    }
}

function showOptionsModal(product) {
    const sizeOptions = document.getElementById('sizeOptions');
    const colorOptions = document.getElementById('colorOptions');
    const sizeButtons = document.getElementById('sizeButtons');
    const colorButtons = document.getElementById('colorButtons');
    
    sizeButtons.innerHTML = '';
    colorButtons.innerHTML = '';
    
    if (product.sizes && product.sizes.length > 0) {
        sizeOptions.style.display = 'block';
        product.sizes.forEach((size, i) => {
            sizeButtons.innerHTML += `
                <input type="radio" class="btn-check" name="productSize" id="size${i}" value="${size}" ${i===0?'checked':''}>
                <label class="btn btn-outline-primary" for="size${i}">${size}</label>
            `;
        });
    } else {
        sizeOptions.style.display = 'none';
    }
    
    if (product.colors && product.colors.length > 0) {
        colorOptions.style.display = 'block';
        product.colors.forEach((color, i) => {
            colorButtons.innerHTML += `
                <input type="radio" class="btn-check" name="productColor" id="color${i}" value="${color}" ${i===0?'checked':''}>
                <label class="btn btn-outline-primary" for="color${i}">${color}</label>
            `;
        });
    } else {
        colorOptions.style.display = 'none';
    }
    
    document.getElementById('optionQty').value = 1;
    new bootstrap.Modal(document.getElementById('optionsModal')).show();
}

function confirmAddToCart() {
    const size = document.querySelector('input[name="productSize"]:checked')?.value;
    const color = document.querySelector('input[name="productColor"]:checked')?.value;
    const qty = parseInt(document.getElementById('optionQty').value) || 1;
    
    const options = {};
    if (size) options.size = size;
    if (color) options.color = color;
    
    addProductToCart(pendingProduct, qty, options);
    bootstrap.Modal.getInstance(document.getElementById('optionsModal')).hide();
}

function addProductToCart(product, qty, options) {
    const cartKey = `${product.id}-${JSON.stringify(options)}`;
    const existing = cart.find(item => item.cartKey === cartKey);
    
    if (existing) {
        existing.quantity += qty;
    } else {
        cart.push({
            cartKey,
            productId: product.id,
            name: product.name,
            price: product.price,
            quantity: qty,
            options
        });
    }
    
    updateCartDisplay();
}

function updateCartDisplay() {
    const cartItemsEl = document.getElementById('cartItems');
    const emptyCart = document.getElementById('emptyCart');
    
    if (cart.length === 0) {
        cartItemsEl.innerHTML = emptyCart.outerHTML;
        document.getElementById('checkoutBtn').disabled = true;
    } else {
        let html = '';
        cart.forEach((item, index) => {
            const optionsText = Object.entries(item.options).map(([k,v]) => `${v}`).join(', ');
            html += `
                <div class="cart-item">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h6 class="mb-0">${item.name}</h6>
                            ${optionsText ? `<small class="text-muted">${optionsText}</small>` : ''}
                            <div class="text-primary">$${item.price.toFixed(2)}</div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button class="btn btn-outline-secondary qty-btn" onclick="updateQty(${index}, -1)">-</button>
                            <span class="fw-bold">${item.quantity}</span>
                            <button class="btn btn-outline-secondary qty-btn" onclick="updateQty(${index}, 1)">+</button>
                            <button class="btn btn-outline-danger qty-btn" onclick="removeItem(${index})">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>
                    <div class="text-end fw-bold">$${(item.price * item.quantity).toFixed(2)}</div>
                </div>
            `;
        });
        cartItemsEl.innerHTML = html;
        document.getElementById('checkoutBtn').disabled = false;
    }
    
    updateTotals();
}

function updateQty(index, delta) {
    cart[index].quantity += delta;
    if (cart[index].quantity <= 0) {
        cart.splice(index, 1);
    }
    updateCartDisplay();
}

function removeItem(index) {
    cart.splice(index, 1);
    updateCartDisplay();
}

function clearCart() {
    if (cart.length === 0) return;
    if (confirm('Clear all items from cart?')) {
        cart = [];
        updateCartDisplay();
    }
}

function updateTotals() {
    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const discount = parseFloat(document.getElementById('discountAmount').value) || 0;
    const tax = (subtotal - discount) * (taxRate / 100);
    const total = subtotal - discount + tax;
    
    document.getElementById('cartSubtotal').textContent = '$' + subtotal.toFixed(2);
    document.getElementById('cartTax').textContent = '$' + tax.toFixed(2);
    document.getElementById('cartTotal').textContent = '$' + total.toFixed(2);
}

document.getElementById('discountAmount').addEventListener('input', updateTotals);

function processOrder() {
    if (cart.length === 0) return;
    
    const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked').value;
    const discount = parseFloat(document.getElementById('discountAmount').value) || 0;
    
    const orderData = {
        items: cart,
        payment_method: paymentMethod,
        discount_amount: discount,
        _token: '{{ csrf_token() }}'
    };
    
    document.getElementById('checkoutBtn').disabled = true;
    document.getElementById('checkoutBtn').innerHTML = '<span class="spinner-border spinner-border-sm"></span> Processing...';
    
    fetch('{{ route("store-owner.pos.process") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(orderData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            cart = [];
            updateCartDisplay();
            document.getElementById('discountAmount').value = 0;
            
            // Open receipt in new tab
            window.open(data.receipt_url, '_blank');
            
            alert('Order completed successfully! Order #' + data.order_number);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error processing order. Please try again.');
        console.error(error);
    })
    .finally(() => {
        document.getElementById('checkoutBtn').disabled = false;
        document.getElementById('checkoutBtn').innerHTML = '<i class="bi bi-check-circle me-1"></i>Complete Order';
    });
}

updateCartDisplay();
</script>
@endsection
