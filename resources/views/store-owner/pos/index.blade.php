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
    /* QR Scanner Styles */
    #qr-reader {
        width: 100%;
        max-width: 400px;
        margin: 0 auto;
    }
    #qr-reader video {
        border-radius: 8px;
    }
    .scanner-section {
        background: #1a1a2e;
        border-radius: 12px;
        padding: 20px;
    }
    .scanned-order-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }
    .order-status-badge {
        font-size: 0.9rem;
        padding: 8px 16px;
        border-radius: 20px;
    }
    .action-btn {
        padding: 12px 24px;
        font-size: 1rem;
        border-radius: 8px;
    }
</style>
@endpush

@section('content')
<!-- Tab Navigation -->
<ul class="nav nav-tabs mb-4" id="posTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="products-tab" data-bs-toggle="tab" data-bs-target="#products-panel" type="button">
            <i class="bi bi-grid me-2"></i>Quick Sale
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="scanner-tab" data-bs-toggle="tab" data-bs-target="#scanner-panel" type="button">
            <i class="bi bi-qr-code-scan me-2"></i>Scan Order QR
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending-panel" type="button">
            <i class="bi bi-clock-history me-2"></i>Pending Orders
            @if($pendingOrders->count() > 0)
                <span class="badge bg-danger ms-1">{{ $pendingOrders->count() }}</span>
            @endif
        </button>
    </li>
</ul>

<div class="tab-content" id="posTabsContent">
    <!-- Quick Sale Panel -->
    <div class="tab-pane fade show active" id="products-panel" role="tabpanel">
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
                                         'price' => $product->price,
                                         'image' => $product->image ? asset('storage/' . $product->image) : null,
                                         'stock' => $product->stock_quantity,
                                         'track_stock' => $product->track_inventory,
                                     ]) }})"
                                >
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
                                            ₹{{ number_format($product->price, 2) }}
                                        </p>
                                        @if($product->track_inventory)
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
    </div>

    <!-- QR Scanner Panel -->
    <div class="tab-pane fade" id="scanner-panel" role="tabpanel">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="row g-4">
                    <!-- Scanner Section -->
                    <div class="col-md-6">
                        <div class="scanner-section text-white">
                            <h5 class="mb-3 text-center">
                                <i class="bi bi-qr-code-scan me-2"></i>Scan Order QR Code
                            </h5>
                            <div id="qr-reader"></div>
                            <div class="mt-3 text-center">
                                <button class="btn btn-outline-light btn-sm" id="startScanBtn" onclick="startScanner()">
                                    <i class="bi bi-camera-video me-1"></i>Start Camera
                                </button>
                                <button class="btn btn-outline-light btn-sm d-none" id="stopScanBtn" onclick="stopScanner()">
                                    <i class="bi bi-stop-circle me-1"></i>Stop Camera
                                </button>
                            </div>
                            <div id="scannerStatus" class="text-center mt-3 small">
                                Click "Start Camera" to begin scanning
                            </div>
                        </div>
                    </div>

                    <!-- Scanned Order Display -->
                    <div class="col-md-6">
                        <div id="scannedOrderSection">
                            <div class="card scanned-order-card" id="noOrderScanned">
                                <div class="card-body text-center py-5">
                                    <i class="bi bi-upc-scan text-muted" style="font-size: 4rem;"></i>
                                    <h5 class="mt-3 text-muted">No Order Scanned</h5>
                                    <p class="text-muted">Scan an order verification QR code to see details</p>
                                </div>
                            </div>

                            <div class="card scanned-order-card d-none" id="scannedOrderCard">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Order Details</h5>
                                    <span class="order-status-badge" id="orderStatusBadge"></span>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <small class="text-muted">Order Number</small>
                                            <h6 id="scannedOrderNumber" class="mb-0"></h6>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Customer</small>
                                            <h6 id="scannedCustomerName" class="mb-0"></h6>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <small class="text-muted">Date</small>
                                            <h6 id="scannedOrderDate" class="mb-0"></h6>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Payment</small>
                                            <h6 id="scannedPaymentStatus" class="mb-0"></h6>
                                        </div>
                                    </div>

                                    <hr>

                                    <h6 class="mb-2">Items</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm" id="scannedItemsTable">
                                            <thead>
                                                <tr>
                                                    <th>Item</th>
                                                    <th class="text-center">Qty</th>
                                                    <th class="text-end">Price</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>

                                    <hr>

                                    <div class="d-flex justify-content-between">
                                        <span>Subtotal:</span>
                                        <span id="scannedSubtotal"></span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Tax:</span>
                                        <span id="scannedTax"></span>
                                    </div>
                                    <div class="d-flex justify-content-between" id="scannedDiscountRow">
                                        <span>Discount:</span>
                                        <span id="scannedDiscount"></span>
                                    </div>
                                    <div class="d-flex justify-content-between mt-2">
                                        <strong class="fs-5">Total:</strong>
                                        <strong class="fs-5" id="scannedTotal"></strong>
                                    </div>
                                </div>
                                <div class="card-footer" id="orderActionsSection">
                                    <div class="d-grid gap-2" id="orderActions">
                                        <!-- Actions will be dynamically populated -->
                                    </div>
                                </div>
                            </div>

                            <!-- Error Card -->
                            <div class="card border-danger d-none" id="scanErrorCard">
                                <div class="card-body text-center py-4">
                                    <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 3rem;"></i>
                                    <h5 class="mt-3 text-danger">Scan Failed</h5>
                                    <p class="text-muted" id="scanErrorMessage"></p>
                                    <button class="btn btn-outline-primary btn-sm" onclick="resetScanner()">
                                        <i class="bi bi-arrow-repeat me-1"></i>Try Again
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Orders Panel -->
    <div class="tab-pane fade" id="pending-panel" role="tabpanel">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Pending Orders</h5>
            </div>
            <div class="card-body">
                @if($pendingOrders->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Total</th>
                                    <th>Payment</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingOrders as $order)
                                <tr>
                                    <td><strong>{{ $order->order_number }}</strong></td>
                                    <td>{{ $order->customer ? $order->customer->name : 'Walk-in' }}</td>
                                    <td>₹{{ number_format($order->total, 2) }}</td>
                                    <td>
                                        @if($order->payment_status === 'paid')
                                            <span class="badge bg-success">Paid</span>
                                        @else
                                            <span class="badge bg-warning">{{ ucfirst($order->payment_status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ ucfirst($order->order_status) }}</span>
                                    </td>
                                    <td>{{ $order->created_at->format('M d, H:i') }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" onclick="viewPendingOrder({{ $order->id }})">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        @if($order->payment_status !== 'paid')
                                        <button class="btn btn-sm btn-success" onclick="markOrderPaid({{ $order->id }})">
                                            <i class="bi bi-check-lg"></i> Mark Paid
                                        </button>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">No Pending Orders</h5>
                        <p>All orders have been processed</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- html5-qrcode Library -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<script>
let cart = [];
let html5QrcodeScanner = null;
let isScanning = false;
let currentScannedOrder = null;
const taxRate = {{ $taxRate ?? 0 }};

// =====================
// QR SCANNER FUNCTIONS
// =====================

function startScanner() {
    const config = {
        fps: 10,
        qrbox: { width: 250, height: 250 },
        aspectRatio: 1.0,
    };

    html5QrcodeScanner = new Html5Qrcode("qr-reader");
    
    html5QrcodeScanner.start(
        { facingMode: "environment" },
        config,
        onScanSuccess,
        onScanError
    ).then(() => {
        isScanning = true;
        document.getElementById('startScanBtn').classList.add('d-none');
        document.getElementById('stopScanBtn').classList.remove('d-none');
        document.getElementById('scannerStatus').innerHTML = '<span class="text-success"><i class="bi bi-record-circle me-1"></i>Camera active - Position QR code in frame</span>';
    }).catch(err => {
        document.getElementById('scannerStatus').innerHTML = `<span class="text-danger">Error: ${err}</span>`;
    });
}

function stopScanner() {
    if (html5QrcodeScanner && isScanning) {
        html5QrcodeScanner.stop().then(() => {
            isScanning = false;
            document.getElementById('startScanBtn').classList.remove('d-none');
            document.getElementById('stopScanBtn').classList.add('d-none');
            document.getElementById('scannerStatus').textContent = 'Camera stopped';
        });
    }
}

function onScanSuccess(decodedText, decodedResult) {
    // Stop scanning after successful scan
    stopScanner();
    
    document.getElementById('scannerStatus').innerHTML = '<span class="text-info"><i class="bi bi-hourglass-split me-1"></i>Verifying order...</span>';
    
    // Send to server for verification
    fetch('{{ route("store-owner.pos.scan") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ qr_data: decodedText })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayScannedOrder(data.order);
            document.getElementById('scannerStatus').innerHTML = '<span class="text-success"><i class="bi bi-check-circle me-1"></i>Order verified successfully!</span>';
        } else {
            showScanError(data.message);
            document.getElementById('scannerStatus').innerHTML = '<span class="text-danger"><i class="bi bi-x-circle me-1"></i>' + data.message + '</span>';
        }
    })
    .catch(error => {
        showScanError('Failed to verify QR code. Please try again.');
        document.getElementById('scannerStatus').innerHTML = '<span class="text-danger">Network error</span>';
    });
}

function onScanError(errorMessage) {
    // Ignore scan errors (continuous scanning will produce many)
}

function displayScannedOrder(order) {
    currentScannedOrder = order;
    
    document.getElementById('noOrderScanned').classList.add('d-none');
    document.getElementById('scanErrorCard').classList.add('d-none');
    document.getElementById('scannedOrderCard').classList.remove('d-none');
    
    // Populate order details
    document.getElementById('scannedOrderNumber').textContent = order.order_number;
    document.getElementById('scannedCustomerName').textContent = order.customer_name;
    document.getElementById('scannedOrderDate').textContent = order.created_at;
    
    // Payment status
    const paymentEl = document.getElementById('scannedPaymentStatus');
    if (order.payment_status === 'paid') {
        paymentEl.innerHTML = '<span class="text-success">Paid</span>';
    } else {
        paymentEl.innerHTML = `<span class="text-warning">${order.payment_status}</span>`;
    }
    
    // Order status badge
    const statusBadge = document.getElementById('orderStatusBadge');
    const statusColors = {
        'pending': 'bg-warning text-dark',
        'confirmed': 'bg-info text-white',
        'processing': 'bg-primary text-white',
        'completed': 'bg-success text-white',
        'cancelled': 'bg-danger text-white'
    };
    statusBadge.className = 'order-status-badge ' + (statusColors[order.order_status] || 'bg-secondary');
    statusBadge.textContent = order.order_status.charAt(0).toUpperCase() + order.order_status.slice(1);
    
    // Items table
    const tbody = document.querySelector('#scannedItemsTable tbody');
    tbody.innerHTML = '';
    order.items.forEach(item => {
        tbody.innerHTML += `
            <tr>
                <td>${item.name}</td>
                <td class="text-center">${item.quantity}</td>
                <td class="text-end">$${parseFloat(item.subtotal).toFixed(2)}</td>
            </tr>
        `;
    });
    
    // Totals
    document.getElementById('scannedSubtotal').textContent = '$' + parseFloat(order.subtotal).toFixed(2);
    document.getElementById('scannedTax').textContent = '$' + parseFloat(order.tax).toFixed(2);
    
    if (parseFloat(order.discount) > 0) {
        document.getElementById('scannedDiscountRow').classList.remove('d-none');
        document.getElementById('scannedDiscount').textContent = '-$' + parseFloat(order.discount).toFixed(2);
    } else {
        document.getElementById('scannedDiscountRow').classList.add('d-none');
    }
    
    document.getElementById('scannedTotal').textContent = '$' + parseFloat(order.total).toFixed(2);
    
    // Generate action buttons based on order state
    const actionsDiv = document.getElementById('orderActions');
    actionsDiv.innerHTML = '';
    
    if (order.payment_status !== 'paid') {
        actionsDiv.innerHTML += `
            <button class="btn btn-success action-btn" onclick="markScannedOrderPaid()">
                <i class="bi bi-cash me-2"></i>Mark as Paid
            </button>
        `;
    }
    
    if (order.order_status !== 'completed' && order.order_status !== 'cancelled') {
        actionsDiv.innerHTML += `
            <button class="btn btn-primary action-btn" onclick="completeScannedOrder()">
                <i class="bi bi-check-circle me-2"></i>Complete Order
            </button>
        `;
    }
    
    if (order.order_status === 'completed') {
        actionsDiv.innerHTML = `
            <div class="alert alert-success mb-0 text-center">
                <i class="bi bi-check-circle-fill me-2"></i>
                This order is already completed
            </div>
        `;
    }
    
    actionsDiv.innerHTML += `
        <button class="btn btn-outline-secondary" onclick="resetScanner()">
            <i class="bi bi-arrow-repeat me-1"></i>Scan Another
        </button>
    `;
}

function showScanError(message) {
    document.getElementById('noOrderScanned').classList.add('d-none');
    document.getElementById('scannedOrderCard').classList.add('d-none');
    document.getElementById('scanErrorCard').classList.remove('d-none');
    document.getElementById('scanErrorMessage').textContent = message;
}

function resetScanner() {
    currentScannedOrder = null;
    document.getElementById('noOrderScanned').classList.remove('d-none');
    document.getElementById('scannedOrderCard').classList.add('d-none');
    document.getElementById('scanErrorCard').classList.add('d-none');
    document.getElementById('scannerStatus').textContent = 'Click "Start Camera" to begin scanning';
}

function markScannedOrderPaid() {
    if (!currentScannedOrder) return;
    
    fetch(`/store-owner/pos/${currentScannedOrder.id}/mark-paid`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Order marked as paid successfully!');
            currentScannedOrder.payment_status = 'paid';
            currentScannedOrder.order_status = data.order.order_status;
            displayScannedOrder(currentScannedOrder);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Failed to update order. Please try again.');
    });
}

function completeScannedOrder() {
    if (!currentScannedOrder) return;
    
    if (!confirm('Complete this order? It cannot be scanned again after completion.')) {
        return;
    }
    
    fetch(`/store-owner/pos/${currentScannedOrder.id}/complete`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Order completed successfully!');
            currentScannedOrder.order_status = 'completed';
            displayScannedOrder(currentScannedOrder);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Failed to complete order. Please try again.');
    });
}

// =====================
// CART FUNCTIONS
// =====================

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

function addToCart(product) {
    const existing = cart.find(item => item.productId === product.id);
    
    if (existing) {
        existing.quantity += 1;
    } else {
        cart.push({
            productId: product.id,
            name: product.name,
            price: product.price,
            quantity: 1
        });
    }
    
    updateCartDisplay();
}

function updateCartDisplay() {
    const cartItemsEl = document.getElementById('cartItems');
    const emptyCartHtml = `
        <div class="text-center text-muted py-5" id="emptyCart">
            <i class="bi bi-cart fs-1 mb-2 d-block"></i>
            <p>Cart is empty</p>
            <small>Click on products to add them</small>
        </div>
    `;
    
    if (cart.length === 0) {
        cartItemsEl.innerHTML = emptyCartHtml;
        document.getElementById('checkoutBtn').disabled = true;
    } else {
        let html = '';
        cart.forEach((item, index) => {
            html += `
                <div class="cart-item">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h6 class="mb-0">${item.name}</h6>
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
        discount_amount: discount
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

function markOrderPaid(orderId) {
    if (!confirm('Mark this order as paid?')) return;
    
    fetch(`/store-owner/pos/${orderId}/mark-paid`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Order marked as paid!');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    });
}

function viewPendingOrder(orderId) {
    window.location.href = `/store-owner/orders/${orderId}`;
}

// Initialize
updateCartDisplay();
</script>
@endsection
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
                                    ₹{{ number_format($product->sale_price ?? $product->price, 2) }}
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

