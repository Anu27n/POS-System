@extends('layouts.store-owner')

@section('title', 'POS Terminal')
@section('page-title', 'Point of Sale')

@push('styles')
<style>
    .pos-container {
        height: calc(100vh - 140px);
    }
    
    /* Mobile: Stack cart below products */
    @media (max-width: 991px) {
        .pos-container {
            height: auto;
        }
        .pos-container > .col-lg-8,
        .pos-container > .col-lg-4 {
            height: auto;
        }
        .pos-container > .col-lg-4 {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 1040;
            max-height: 50vh;
            padding: 0;
        }
        .pos-container > .col-lg-4 .card {
            border-radius: 16px 16px 0 0;
            box-shadow: 0 -4px 20px rgba(0,0,0,0.15);
        }
        .pos-container > .col-lg-4 .cart-items {
            max-height: 25vh;
        }
        .pos-container > .col-lg-8 {
            padding-bottom: 200px;
        }
        .products-grid {
            height: auto !important;
            max-height: 60vh;
        }
        /* Mobile cart toggle */
        .mobile-cart-toggle {
            display: block !important;
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1050;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            font-size: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }
        .cart-section.collapsed {
            transform: translateY(calc(100% - 60px));
        }
    }

    .products-grid {
        height: 100%;
        overflow-y: auto;
    }

    .product-card {
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
        height: 100%;
        -webkit-tap-highlight-color: transparent;
        user-select: none;
    }

    .product-card:hover,
    .product-card:active {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    
    .product-card:active {
        transform: scale(0.98);
    }

    .product-card img {
        height: 120px;
        object-fit: cover;
    }
    
    @media (max-width: 575px) {
        .product-card img {
            height: 80px;
        }
        .product-card .card-body {
            padding: 8px !important;
        }
        .product-card .card-title {
            font-size: 0.75rem;
            margin-bottom: 4px !important;
        }
        .product-card .card-text {
            font-size: 0.85rem;
        }
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
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
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
    
    .mobile-cart-toggle {
        display: none;
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
                                <div class="card product-card" style="cursor: pointer;"
                                    data-product='{{ json_encode([
                                         "id" => $product->id,
                                         "name" => $product->name,
                                         "price" => $product->price,
                                         "image" => $product->image ? asset("storage/" . $product->image) : null,
                                         "stock" => $product->stock_quantity,
                                         "track_stock" => $product->track_inventory,
                                     ]) }}'>
                                    @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" class="card-img-top" alt="{{ $product->name }}"
                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="bg-light align-items-center justify-content-center" style="height: 120px; display: none;">
                                        <i class="bi bi-box text-muted fs-1"></i>
                                    </div>
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
                                            {{ \App\Helpers\CurrencyHelper::format($product->price, $store->currency ?? 'INR') }}
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

                    <!-- Customer Selection -->
                    <div class="px-3 py-2 border-bottom bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="flex-grow-1">
                                <small class="text-muted">Customer</small>
                                <div id="selectedCustomerDisplay">
                                    <span class="text-muted">Walk-in Customer</span>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#customerModal">
                                <i class="bi bi-person-plus"></i>
                            </button>
                        </div>
                        <input type="hidden" id="selectedCustomerId" value="">
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
                            <span id="cartSubtotal">{{ \App\Helpers\CurrencyHelper::getCurrencySymbol($store->currency ?? 'INR') }}0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax (<span id="taxRate">{{ $taxRate ?? 0 }}</span>%):</span>
                            <span id="cartTax">{{ \App\Helpers\CurrencyHelper::getCurrencySymbol($store->currency ?? 'INR') }}0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Discount:</span>
                            <div class="input-group input-group-sm" style="width: 100px;">
                                <span class="input-group-text">{{ \App\Helpers\CurrencyHelper::getCurrencySymbol($store->currency ?? 'INR') }}</span>
                                <input type="number" class="form-control" id="discountAmount" value="0" min="0" step="0.01">
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <strong class="fs-5">Total:</strong>
                            <strong class="fs-5" id="cartTotal">{{ \App\Helpers\CurrencyHelper::getCurrencySymbol($store->currency ?? 'INR') }}0.00</strong>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Payment Method</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="paymentMethod" id="payCash" value="cash" checked>
                                <label class="btn btn-outline-success" for="payCash">
                                    <i class="bi bi-cash-coin"></i> Cash
                                </label>
                                <input type="radio" class="btn-check" name="paymentMethod" id="payCard" value="card">
                                <label class="btn btn-outline-primary" for="payCard">
                                    <i class="bi bi-credit-card"></i> Card
                                </label>
                                <input type="radio" class="btn-check" name="paymentMethod" id="payUPI" value="upi">
                                <label class="btn btn-outline-info" for="payUPI">
                                    <i class="bi bi-phone"></i> UPI
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
                        
                        <!-- Manual Order Number Entry -->
                        <div class="card mt-4">
                            <div class="card-header bg-secondary text-white">
                                <h6 class="mb-0"><i class="bi bi-keyboard me-2"></i>Manual Order Lookup</h6>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small mb-2">Enter order number if camera scanning doesn't work:</p>
                                <div class="input-group">
                                    <span class="input-group-text">ORD</span>
                                    <input type="text" class="form-control" id="manualOrderNumber" placeholder="e.g., 20260125001" maxlength="20">
                                    <button class="btn btn-primary" type="button" onclick="lookupOrderManually()">
                                        <i class="bi bi-search me-1"></i>Find
                                    </button>
                                </div>
                                <div id="manualLookupStatus" class="mt-2 small"></div>
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
    const currencySymbol = '{{ \App\Helpers\CurrencyHelper::getCurrencySymbol($store->currency ?? "INR") }}';

    // =====================
    // QR SCANNER FUNCTIONS
    // =====================

    function startScanner() {
        const qrReaderElement = document.getElementById("qr-reader");
        const statusElement = document.getElementById('scannerStatus');
        
        if (!qrReaderElement) {
            console.error('qr-reader element not found');
            statusElement.innerHTML = '<span class="text-danger">Scanner element not found</span>';
            return;
        }

        // Check if Html5Qrcode is loaded
        if (typeof Html5Qrcode === 'undefined') {
            console.error('Html5Qrcode library not loaded');
            statusElement.innerHTML = '<span class="text-danger">QR Scanner library not loaded. Please refresh the page.</span>';
            return;
        }

        statusElement.innerHTML = '<span class="text-info"><i class="bi bi-hourglass-split me-1"></i>Requesting camera access...</span>';

        const config = {
            fps: 10,
            qrbox: { width: 250, height: 250 },
            aspectRatio: 1.0,
        };

        // Create new scanner instance
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
            statusElement.innerHTML = '<span class="text-success"><i class="bi bi-record-circle me-1"></i>Camera active - Position QR code in frame</span>';
        }).catch(err => {
            console.error('Camera error:', err);
            let errorMsg = err.toString();
            if (errorMsg.includes('NotAllowedError') || errorMsg.includes('Permission')) {
                statusElement.innerHTML = '<span class="text-danger"><i class="bi bi-exclamation-triangle me-1"></i>Camera permission denied. Please allow camera access and try again.</span>';
            } else if (errorMsg.includes('NotFoundError') || errorMsg.includes('no camera')) {
                statusElement.innerHTML = '<span class="text-danger"><i class="bi bi-exclamation-triangle me-1"></i>No camera found. Use Manual Order Lookup below.</span>';
            } else {
                statusElement.innerHTML = '<span class="text-danger"><i class="bi bi-exclamation-triangle me-1"></i>Camera Error: ' + errorMsg + '</span>';
            }
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

        console.log('QR Scanned! Raw data:', decodedText);
        console.log('Decoded result:', decodedResult);

        document.getElementById('scannerStatus').innerHTML = '<span class="text-info"><i class="bi bi-hourglass-split me-1"></i>Verifying order...</span>';

        // Send to server for verification
        fetch('{{ route("store-owner.pos.scan") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    qr_data: decodedText
                })
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Server response:', data);
                if (data.success) {
                    displayScannedOrder(data.order);
                    document.getElementById('scannerStatus').innerHTML = '<span class="text-success"><i class="bi bi-check-circle me-1"></i>Order verified successfully!</span>';
                } else {
                    showScanError(data.message);
                    document.getElementById('scannerStatus').innerHTML = '<span class="text-danger"><i class="bi bi-x-circle me-1"></i>' + data.message + '</span>';
                }
            })
            .catch(error => {
                console.error('Network error:', error);
                showScanError('Failed to verify QR code. Please try again.');
                document.getElementById('scannerStatus').innerHTML = '<span class="text-danger">Network error</span>';
            });
    }

    function onScanError(errorMessage) {
        // Ignore scan errors (continuous scanning will produce many)
        // Uncomment to debug: console.log('Scan error:', errorMessage);
    }

    // Manual order lookup function
    function lookupOrderManually() {
        const orderInput = document.getElementById('manualOrderNumber');
        const statusDiv = document.getElementById('manualLookupStatus');
        const orderNumber = orderInput.value.trim();

        if (!orderNumber) {
            statusDiv.innerHTML = '<span class="text-danger">Please enter an order number</span>';
            return;
        }

        statusDiv.innerHTML = '<span class="text-info"><i class="bi bi-hourglass-split me-1"></i>Looking up order...</span>';

        fetch(`{{ route("store-owner.pos.order.lookup") }}?order_number=${encodeURIComponent(orderNumber)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayScannedOrder(data.order);
                    statusDiv.innerHTML = '<span class="text-success"><i class="bi bi-check-circle me-1"></i>Order found!</span>';
                    orderInput.value = '';
                } else {
                    showScanError(data.message);
                    statusDiv.innerHTML = `<span class="text-danger"><i class="bi bi-x-circle me-1"></i>${data.message}</span>`;
                }
            })
            .catch(error => {
                statusDiv.innerHTML = '<span class="text-danger">Network error. Please try again.</span>';
            });
    }

    // Allow Enter key to trigger lookup
    document.addEventListener('DOMContentLoaded', function() {
        const orderInput = document.getElementById('manualOrderNumber');
        if (orderInput) {
            orderInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    lookupOrderManually();
                }
            });
        }
    });

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
                <td class="text-end">₹${parseFloat(item.subtotal).toFixed(2)}</td>
            </tr>
        `;
        });

        // Totals
        document.getElementById('scannedSubtotal').textContent = '₹' + parseFloat(order.subtotal).toFixed(2);
        document.getElementById('scannedTax').textContent = '₹' + parseFloat(order.tax).toFixed(2);

        if (parseFloat(order.discount) > 0) {
            document.getElementById('scannedDiscountRow').classList.remove('d-none');
            document.getElementById('scannedDiscount').textContent = '-₹' + parseFloat(order.discount).toFixed(2);
        } else {
            document.getElementById('scannedDiscountRow').classList.add('d-none');
        }

        document.getElementById('scannedTotal').textContent = '₹' + parseFloat(order.total).toFixed(2);

        // Generate action buttons based on order state
        const actionsDiv = document.getElementById('orderActions');
        actionsDiv.innerHTML = '';

        if (order.payment_status !== 'paid') {
            actionsDiv.innerHTML += `
            <div class="mb-3">
                <label class="form-label fw-bold">Select Payment Method</label>
                <div class="btn-group w-100" role="group">
                    <input type="radio" class="btn-check" name="scannedPaymentMethod" id="scanPayCash" value="cash" checked>
                    <label class="btn btn-outline-success" for="scanPayCash">
                        <i class="bi bi-cash-coin me-1"></i>Cash
                    </label>
                    <input type="radio" class="btn-check" name="scannedPaymentMethod" id="scanPayCard" value="card">
                    <label class="btn btn-outline-primary" for="scanPayCard">
                        <i class="bi bi-credit-card me-1"></i>Card
                    </label>
                    <input type="radio" class="btn-check" name="scannedPaymentMethod" id="scanPayUPI" value="upi">
                    <label class="btn btn-outline-info" for="scanPayUPI">
                        <i class="bi bi-phone me-1"></i>UPI
                    </label>
                </div>
            </div>
            <button class="btn btn-success action-btn w-100" onclick="markScannedOrderPaid()">
                <i class="bi bi-check-circle me-2"></i>Accept Payment & Mark Paid
            </button>
        `;
        }

        // Always show print receipt button if order exists
        if (order.id) {
            actionsDiv.innerHTML += `
            <a href="/store-owner/orders/${order.id}/receipt" target="_blank" class="btn btn-outline-dark action-btn w-100">
                <i class="bi bi-printer me-2"></i>Print Bill / Receipt
            </a>
        `;
        }

        if (order.payment_status === 'paid' && order.order_status !== 'completed' && order.order_status !== 'cancelled') {
            actionsDiv.innerHTML += `
            <button class="btn btn-primary action-btn w-100" onclick="completeScannedOrder()">
                <i class="bi bi-box-seam me-2"></i>Complete Order (Handover)
            </button>
        `;
        }

        if (order.order_status === 'completed') {
            actionsDiv.innerHTML = `
            <div class="alert alert-success mb-3 text-center">
                <i class="bi bi-check-circle-fill me-2"></i>
                This order is completed
            </div>
            <a href="/store-owner/orders/${order.id}/receipt" target="_blank" class="btn btn-outline-dark action-btn w-100">
                <i class="bi bi-printer me-2"></i>Print Bill / Receipt
            </a>
        `;
        }

        actionsDiv.innerHTML += `
        <button class="btn btn-outline-secondary w-100" onclick="resetScanner()">
            <i class="bi bi-arrow-repeat me-1"></i>Scan Another Order
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

        const paymentMethod = document.querySelector('input[name="scannedPaymentMethod"]:checked')?.value || 'cash';

        if (!confirm(`Accept payment via ${paymentMethod.toUpperCase()} and mark order as paid?`)) {
            return;
        }

        fetch(`/store-owner/pos/${currentScannedOrder.id}/mark-paid`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    payment_method: paymentMethod
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update local order object
                    currentScannedOrder.payment_status = 'paid';
                    currentScannedOrder.payment_method = paymentMethod;
                    currentScannedOrder.order_status = data.order.order_status;

                    // Show success and refresh display
                    displayScannedOrder(currentScannedOrder);

                    // Ask if they want to print receipt
                    if (confirm('Payment received! Would you like to print the receipt?')) {
                        window.open(data.order.receipt_url, '_blank');
                    }
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

    // Product card click handler with touch support
    document.getElementById('productsGrid').addEventListener('click', function(e) {
        console.log('Click detected on:', e.target);
        const productCard = e.target.closest('.product-card');
        console.log('Product card found:', productCard);
        if (productCard && productCard.dataset.product) {
            e.preventDefault();
            e.stopPropagation();
            try {
                console.log('Product data:', productCard.dataset.product);
                const product = JSON.parse(productCard.dataset.product);
                console.log('Parsed product:', product);
                addToCart(product);
                // Visual feedback
                productCard.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    productCard.style.transform = '';
                }, 150);
            } catch (err) {
                console.error('Error parsing product data:', err);
                alert('Error adding product: ' + err.message);
            }
        } else {
            console.log('No product card or data-product attribute found');
        }
    });

    // Touch support for mobile
    document.getElementById('productsGrid').addEventListener('touchend', function(e) {
        const productCard = e.target.closest('.product-card');
        if (productCard && productCard.dataset.product) {
            e.preventDefault();
            try {
                const product = JSON.parse(productCard.dataset.product);
                addToCart(product);
                // Visual feedback
                productCard.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    productCard.style.transform = '';
                }, 150);
            } catch (err) {
                console.error('Error parsing product data:', err);
            }
        }
    }, { passive: false });

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
                price: parseFloat(product.price),
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
                            <div class="text-primary">${currencySymbol}${item.price.toFixed(2)}</div>
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
                    <div class="text-end fw-bold">${currencySymbol}${(item.price * item.quantity).toFixed(2)}</div>
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

        document.getElementById('cartSubtotal').textContent = currencySymbol + subtotal.toFixed(2);
        document.getElementById('cartTax').textContent = currencySymbol + tax.toFixed(2);
        document.getElementById('cartTotal').textContent = currencySymbol + total.toFixed(2);
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
            customer_id: document.getElementById('selectedCustomerId').value || null
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

    // Customer Search Functionality
    let customerSearchTimeout;
    const customerSearchInput = document.getElementById('customerSearchInput');
    const customerSearchResults = document.getElementById('customerSearchResults');

    if (customerSearchInput) {
        customerSearchInput.addEventListener('input', function() {
            clearTimeout(customerSearchTimeout);
            const query = this.value.trim();

            if (query.length < 2) {
                customerSearchResults.innerHTML = '<div class="text-muted text-center py-3">Type at least 2 characters to search</div>';
                return;
            }

            customerSearchResults.innerHTML = '<div class="text-center py-3"><span class="spinner-border spinner-border-sm"></span> Searching...</div>';

            customerSearchTimeout = setTimeout(() => {
                fetch(`{{ route('store-owner.pos.customers.search') }}?q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.customers.length === 0) {
                            customerSearchResults.innerHTML = '<div class="text-muted text-center py-3">No customers found</div>';
                            return;
                        }

                        customerSearchResults.innerHTML = data.customers.map(customer => `
                            <div class="customer-result p-2 border-bottom" style="cursor:pointer" onclick="selectCustomer(${customer.id}, '${customer.name}', '${customer.phone || ''}')">
                                <div class="fw-semibold">${customer.name}</div>
                                <small class="text-muted">${customer.phone || ''} ${customer.email ? '• ' + customer.email : ''}</small>
                            </div>
                        `).join('');
                    })
                    .catch(error => {
                        customerSearchResults.innerHTML = '<div class="text-danger text-center py-3">Error searching customers</div>';
                    });
            }, 300);
        });
    }

    function selectCustomer(id, name, phone) {
        document.getElementById('selectedCustomerId').value = id;
        document.getElementById('selectedCustomerDisplay').innerHTML = `
            <strong>${name}</strong>
            ${phone ? '<small class="text-muted d-block">' + phone + '</small>' : ''}
        `;
        bootstrap.Modal.getInstance(document.getElementById('customerModal')).hide();
    }

    function clearSelectedCustomer() {
        document.getElementById('selectedCustomerId').value = '';
        document.getElementById('selectedCustomerDisplay').innerHTML = '<span class="text-muted">Walk-in Customer</span>';
    }

    // New Customer Form
    const newCustomerForm = document.getElementById('newCustomerForm');
    if (newCustomerForm) {
        newCustomerForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Creating...';

            fetch('{{ route('store-owner.pos.customers.create') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        selectCustomer(data.customer.id, data.customer.name, data.customer.phone);
                        this.reset();
                        // Close the modal after successful creation
                        const modal = bootstrap.Modal.getInstance(document.getElementById('customerModal'));
                        if (modal) modal.hide();
                        alert('Customer added successfully!');
                    } else {
                        alert('Error: ' + (data.message || 'Failed to create customer'));
                    }
                })
                .catch(error => {
                    alert('Error creating customer');
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="bi bi-person-plus me-1"></i> Add Customer';
                });
        });
    }
</script>

<!-- Customer Selection Modal -->
<div class="modal fade" id="customerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person me-2"></i>Select Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="searchCustomerTab" data-bs-toggle="tab" data-bs-target="#searchCustomerPane">
                            <i class="bi bi-search me-1"></i> Search
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#newCustomerPane">
                            <i class="bi bi-person-plus me-1"></i> New Customer
                        </button>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="searchCustomerPane">
                        <input type="text" class="form-control mb-3" id="customerSearchInput" placeholder="Search by name, phone, or email...">
                        <div id="customerSearchResults" style="max-height: 300px; overflow-y: auto;">
                            <div class="text-muted text-center py-3">Type to search for customers</div>
                        </div>
                        <div class="mt-3">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearSelectedCustomer()" data-bs-dismiss="modal">
                                <i class="bi bi-x me-1"></i> Use Walk-in Customer
                            </button>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="newCustomerPane">
                        <form id="newCustomerForm">
                            <div class="mb-3">
                                <label class="form-label">Customer Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" name="phone" placeholder="+91 9876543210">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" placeholder="customer@example.com">
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-person-plus me-1"></i> Add Customer
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cash Register Modal (shown when no session is open) -->
@if(!isset($cashRegisterSession))
<div class="modal fade" id="cashRegisterModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-cash-stack me-2"></i>Open Cash Register</h5>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    You need to open a cash register session before processing orders.
                </div>
                <form action="{{ route('store-owner.cash-register.open') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Opening Cash Amount <span class="text-danger">*</span></label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text">{{ \App\Helpers\CurrencyHelper::getCurrencySymbol() ?? '₹' }}</span>
                            <input type="number" step="0.01" class="form-control" name="opening_cash" placeholder="0.00" required autofocus>
                        </div>
                        <small class="text-muted">Enter the amount of cash currently in the drawer</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" name="notes" rows="2" placeholder="Any notes about this session..."></textarea>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-unlock me-2"></i> Open Register & Start Selling
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var cashRegisterModal = new bootstrap.Modal(document.getElementById('cashRegisterModal'));
        cashRegisterModal.show();
    });
</script>
@endif
@endsection