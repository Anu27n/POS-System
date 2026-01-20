@extends('layouts.admin')

@section('title', 'Order Scanner')
@section('page-title', 'Order Verification Scanner')

@push('styles')
<style>
    .scanner-container {
        max-width: 1000px;
        margin: 0 auto;
    }

    .scanner-section {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
        border-radius: 16px;
        padding: 24px;
        color: white;
    }

    #qr-reader {
        width: 100%;
        max-width: 350px;
        margin: 0 auto;
        border-radius: 12px;
        overflow: hidden;
    }

    #qr-reader video {
        border-radius: 12px;
    }

    .order-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .order-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
    }

    .status-badge {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }

    .status-confirmed {
        background: #dbeafe;
        color: #1e40af;
    }

    .status-processing {
        background: #e0e7ff;
        color: #3730a3;
    }

    .status-completed {
        background: #d1fae5;
        color: #065f46;
    }

    .status-cancelled {
        background: #fee2e2;
        color: #991b1b;
    }

    .payment-paid {
        background: #d1fae5;
        color: #065f46;
    }

    .payment-pending {
        background: #fef3c7;
        color: #92400e;
    }

    .payment-failed {
        background: #fee2e2;
        color: #991b1b;
    }

    .action-btn {
        padding: 12px 24px;
        font-size: 1rem;
        border-radius: 8px;
        font-weight: 500;
    }

    .items-table th {
        background: #f8fafc;
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
</style>
@endpush

@section('content')
<div class="scanner-container">
    <div class="row g-4">
        <!-- Scanner Section -->
        <div class="col-lg-5">
            <div class="scanner-section h-100">
                <div class="text-center mb-4">
                    <i class="bi bi-qr-code-scan" style="font-size: 2.5rem;"></i>
                    <h4 class="mt-2 mb-1">Scan Order QR Code</h4>
                    <p class="text-white-50 mb-0 small">Position the QR code within the camera frame</p>
                </div>

                <div id="qr-reader" class="mb-3"></div>

                <div class="d-flex justify-content-center gap-2 mb-3">
                    <button class="btn btn-outline-light" id="startScanBtn" onclick="startScanner()">
                        <i class="bi bi-camera-video me-1"></i>Start Camera
                    </button>
                    <button class="btn btn-outline-light d-none" id="stopScanBtn" onclick="stopScanner()">
                        <i class="bi bi-stop-circle me-1"></i>Stop
                    </button>
                </div>

                <div id="scannerStatus" class="text-center small text-white-50">
                    Click "Start Camera" to begin scanning
                </div>

                <!-- Manual Entry Option -->
                <div class="mt-4 pt-4 border-top border-secondary">
                    <p class="text-center text-white-50 small mb-2">Or enter order number manually:</p>
                    <div class="input-group">
                        <input type="text" class="form-control" id="manualOrderNumber" placeholder="ORD-XXXXXXXX">
                        <button class="btn btn-light" onclick="searchOrderManually()">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Details Section -->
        <div class="col-lg-7">
            <!-- Empty State -->
            <div class="order-card" id="emptyState">
                <div class="card-body text-center py-5">
                    <i class="bi bi-upc-scan text-muted" style="font-size: 4rem;"></i>
                    <h4 class="mt-3 text-muted">No Order Scanned</h4>
                    <p class="text-muted mb-0">Scan an order verification QR code to see details</p>
                </div>
            </div>

            <!-- Order Details -->
            <div class="order-card d-none" id="orderDetails">
                <div class="order-header">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h4 class="mb-1" id="orderNumber"></h4>
                            <p class="mb-0 opacity-75" id="orderDate"></p>
                        </div>
                        <span class="status-badge" id="orderStatusBadge"></span>
                    </div>
                </div>

                <div class="card-body p-4">
                    <!-- Store & Customer Info -->
                    <div class="row mb-4">
                        <div class="col-6">
                            <label class="text-muted small text-uppercase">Store</label>
                            <h6 id="storeName" class="mb-0"></h6>
                        </div>
                        <div class="col-6">
                            <label class="text-muted small text-uppercase">Customer</label>
                            <h6 id="customerName" class="mb-0"></h6>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-6">
                            <label class="text-muted small text-uppercase">Payment Method</label>
                            <h6 id="paymentMethod" class="mb-0"></h6>
                        </div>
                        <div class="col-6">
                            <label class="text-muted small text-uppercase">Payment Status</label>
                            <span class="status-badge" id="paymentStatusBadge"></span>
                        </div>
                    </div>

                    <!-- Items -->
                    <h6 class="text-uppercase text-muted small mb-3">Order Items</h6>
                    <div class="table-responsive mb-4">
                        <table class="table table-sm items-table">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="orderItems"></tbody>
                        </table>
                    </div>

                    <!-- Totals -->
                    <div class="bg-light rounded p-3 mb-4">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Subtotal</span>
                            <span id="subtotal"></span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Tax</span>
                            <span id="tax"></span>
                        </div>
                        <div class="d-flex justify-content-between mb-1 d-none" id="discountRow">
                            <span>Discount</span>
                            <span id="discount"></span>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between">
                            <strong class="fs-5">Total</strong>
                            <strong class="fs-5" id="total"></strong>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div id="orderActions" class="d-grid gap-2">
                        <!-- Dynamic action buttons -->
                    </div>
                </div>
            </div>

            <!-- Error State -->
            <div class="order-card d-none" id="errorState">
                <div class="card-body text-center py-5">
                    <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 3rem;"></i>
                    <h4 class="mt-3 text-danger">Verification Failed</h4>
                    <p class="text-muted" id="errorMessage"></p>
                    <button class="btn btn-outline-primary" onclick="resetScanner()">
                        <i class="bi bi-arrow-repeat me-1"></i>Try Again
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- html5-qrcode Library (CDN - No Node.js required) -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<script>
    let html5QrcodeScanner = null;
    let isScanning = false;
    let currentOrder = null;

    function startScanner() {
        const config = {
            fps: 10,
            qrbox: {
                width: 250,
                height: 250
            },
            aspectRatio: 1.0,
        };

        html5QrcodeScanner = new Html5Qrcode("qr-reader");

        html5QrcodeScanner.start({
                facingMode: "environment"
            },
            config,
            onScanSuccess,
            () => {} // Ignore continuous scan errors
        ).then(() => {
            isScanning = true;
            document.getElementById('startScanBtn').classList.add('d-none');
            document.getElementById('stopScanBtn').classList.remove('d-none');
            document.getElementById('scannerStatus').innerHTML =
                '<span class="text-success"><i class="bi bi-record-circle me-1"></i>Camera active</span>';
        }).catch(err => {
            document.getElementById('scannerStatus').innerHTML =
                `<span class="text-danger">Error: ${err}</span>`;
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

    function onScanSuccess(decodedText) {
        stopScanner();
        document.getElementById('scannerStatus').innerHTML =
            '<span class="text-info"><i class="bi bi-hourglass-split me-1"></i>Verifying...</span>';

        verifyQRCode(decodedText);
    }

    function verifyQRCode(qrData) {
        fetch('{{ route("admin.orders.scan") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    qr_data: qrData
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayOrder(data.order, data.already_completed);
                    document.getElementById('scannerStatus').innerHTML =
                        '<span class="text-success"><i class="bi bi-check-circle me-1"></i>Verified!</span>';
                } else {
                    showError(data.message);
                }
            })
            .catch(error => {
                showError('Network error. Please try again.');
            });
    }

    function searchOrderManually() {
        const orderNumber = document.getElementById('manualOrderNumber').value.trim();
        if (!orderNumber) {
            alert('Please enter an order number');
            return;
        }

        window.location.href = '/admin/orders?search=' + encodeURIComponent(orderNumber);
    }

    function displayOrder(order, alreadyCompleted = false) {
        currentOrder = order;

        document.getElementById('emptyState').classList.add('d-none');
        document.getElementById('errorState').classList.add('d-none');
        document.getElementById('orderDetails').classList.remove('d-none');

        // Populate order details
        document.getElementById('orderNumber').textContent = order.order_number;
        document.getElementById('orderDate').textContent = order.created_at;
        document.getElementById('storeName').textContent = order.store_name;
        document.getElementById('customerName').textContent = order.customer_name;
        document.getElementById('paymentMethod').textContent = order.payment_method.toUpperCase();

        // Status badges
        const statusBadge = document.getElementById('orderStatusBadge');
        statusBadge.className = 'status-badge status-' + order.order_status;
        statusBadge.textContent = order.order_status.charAt(0).toUpperCase() + order.order_status.slice(1);

        const paymentBadge = document.getElementById('paymentStatusBadge');
        paymentBadge.className = 'status-badge payment-' + order.payment_status;
        paymentBadge.textContent = order.payment_status.charAt(0).toUpperCase() + order.payment_status.slice(1);

        // Items
        const itemsBody = document.getElementById('orderItems');
        itemsBody.innerHTML = '';
        order.items.forEach(item => {
            itemsBody.innerHTML += `
            <tr>
                <td>${item.name}</td>
                <td class="text-center">${item.quantity}</td>
                <td class="text-end">$${parseFloat(item.price).toFixed(2)}</td>
                <td class="text-end">$${parseFloat(item.subtotal).toFixed(2)}</td>
            </tr>
        `;
        });

        // Totals
        document.getElementById('subtotal').textContent = '$' + parseFloat(order.subtotal).toFixed(2);
        document.getElementById('tax').textContent = '$' + parseFloat(order.tax).toFixed(2);
        document.getElementById('total').textContent = '$' + parseFloat(order.total).toFixed(2);

        if (parseFloat(order.discount) > 0) {
            document.getElementById('discountRow').classList.remove('d-none');
            document.getElementById('discount').textContent = '-$' + parseFloat(order.discount).toFixed(2);
        } else {
            document.getElementById('discountRow').classList.add('d-none');
        }

        // Actions
        const actionsDiv = document.getElementById('orderActions');
        actionsDiv.innerHTML = '';

        if (alreadyCompleted) {
            actionsDiv.innerHTML = `
            <div class="alert alert-success mb-0 text-center">
                <i class="bi bi-check-circle-fill me-2"></i>
                This order has already been completed
            </div>
        `;
        } else {
            if (order.payment_status !== 'paid') {
                actionsDiv.innerHTML += `
                <button class="btn btn-success action-btn" onclick="markPaid()">
                    <i class="bi bi-cash me-2"></i>Mark as Paid
                </button>
            `;
            }

            if (order.order_status !== 'completed' && order.order_status !== 'cancelled') {
                actionsDiv.innerHTML += `
                <button class="btn btn-primary action-btn" onclick="completeOrder()">
                    <i class="bi bi-check-circle me-2"></i>Complete Order
                </button>
            `;
            }
        }

        actionsDiv.innerHTML += `
        <button class="btn btn-outline-secondary" onclick="resetScanner()">
            <i class="bi bi-arrow-repeat me-1"></i>Scan Another
        </button>
        <a href="/admin/orders/${order.id}" class="btn btn-outline-primary">
            <i class="bi bi-eye me-1"></i>View Full Details
        </a>
    `;
    }

    function showError(message) {
        document.getElementById('emptyState').classList.add('d-none');
        document.getElementById('orderDetails').classList.add('d-none');
        document.getElementById('errorState').classList.remove('d-none');
        document.getElementById('errorMessage').textContent = message;
        document.getElementById('scannerStatus').innerHTML =
            '<span class="text-danger"><i class="bi bi-x-circle me-1"></i>Failed</span>';
    }

    function resetScanner() {
        currentOrder = null;
        document.getElementById('emptyState').classList.remove('d-none');
        document.getElementById('orderDetails').classList.add('d-none');
        document.getElementById('errorState').classList.add('d-none');
        document.getElementById('scannerStatus').textContent = 'Click "Start Camera" to begin scanning';
    }

    function markPaid() {
        if (!currentOrder) return;

        fetch(`/admin/orders/${currentOrder.id}/mark-paid`, {
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
                    displayOrder(data.order, false);
                } else {
                    alert('Error: ' + data.message);
                }
            });
    }

    function completeOrder() {
        if (!currentOrder) return;

        if (!confirm('Complete this order? It cannot be scanned again after completion.')) {
            return;
        }

        fetch(`/admin/orders/${currentOrder.id}/complete`, {
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
                    displayOrder(data.order, true);
                } else {
                    alert('Error: ' + data.message);
                }
            });
    }
</script>
@endsection