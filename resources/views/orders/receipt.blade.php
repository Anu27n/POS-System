<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt - Order #{{ $order->order_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            padding: 20px;
        }
        .receipt {
            max-width: 300px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            padding-bottom: 15px;
            border-bottom: 1px dashed #ccc;
            margin-bottom: 15px;
        }
        .store-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .store-info {
            font-size: 10px;
            color: #666;
        }
        .order-info {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px dashed #ccc;
        }
        .order-info table {
            width: 100%;
        }
        .order-info td {
            padding: 2px 0;
        }
        .order-info .label {
            color: #666;
        }
        .order-info .value {
            text-align: right;
            font-weight: 500;
        }
        .items {
            margin-bottom: 15px;
        }
        .items table {
            width: 100%;
            border-collapse: collapse;
        }
        .items th {
            border-bottom: 1px solid #ccc;
            padding: 5px 0;
            text-align: left;
            font-weight: 600;
        }
        .items th:last-child {
            text-align: right;
        }
        .items td {
            padding: 8px 0;
            vertical-align: top;
            border-bottom: 1px dotted #eee;
        }
        .items td:last-child {
            text-align: right;
        }
        .item-name {
            font-weight: 500;
        }
        .item-options {
            font-size: 10px;
            color: #666;
        }
        .item-qty {
            font-size: 10px;
            color: #666;
        }
        .totals {
            margin-bottom: 15px;
            padding-top: 10px;
            border-top: 1px dashed #ccc;
        }
        .totals table {
            width: 100%;
        }
        .totals td {
            padding: 3px 0;
        }
        .totals .label {
            color: #666;
        }
        .totals .value {
            text-align: right;
        }
        .totals .total-row td {
            font-size: 16px;
            font-weight: bold;
            padding-top: 10px;
            border-top: 1px solid #333;
        }
        .payment-info {
            background: #f5f5f5;
            padding: 10px;
            margin-bottom: 15px;
            text-align: center;
        }
        .payment-method {
            font-weight: bold;
            text-transform: uppercase;
        }
        .payment-status {
            font-size: 11px;
            color: #666;
        }
        .qr-section {
            text-align: center;
            padding: 15px 0;
            border-top: 1px dashed #ccc;
            border-bottom: 1px dashed #ccc;
            margin-bottom: 15px;
        }
        .qr-code img {
            width: 100px;
            height: 100px;
        }
        .verification-code {
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 2px;
            margin-top: 5px;
        }
        .footer {
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .footer p {
            margin: 5px 0;
        }
        .thank-you {
            font-size: 14px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="receipt">
        <!-- Header -->
        <div class="header">
            <div class="store-name">{{ $order->store->name ?? 'Store' }}</div>
            @if($order->store)
                <div class="store-info">
                    @if($order->store->address)
                        {{ $order->store->address }}<br>
                    @endif
                    @if($order->store->phone)
                        Tel: {{ $order->store->phone }}
                    @endif
                </div>
            @endif
        </div>
        
        <!-- Order Info -->
        <div class="order-info">
            <table>
                <tr>
                    <td class="label">Order #</td>
                    <td class="value">{{ $order->order_number }}</td>
                </tr>
                <tr>
                    <td class="label">Date</td>
                    <td class="value">{{ $order->created_at->format('M d, Y') }}</td>
                </tr>
                <tr>
                    <td class="label">Time</td>
                    <td class="value">{{ $order->created_at->format('H:i') }}</td>
                </tr>
                @if($order->user)
                <tr>
                    <td class="label">Customer</td>
                    <td class="value">{{ $order->user->name }}</td>
                </tr>
                @endif
            </table>
        </div>
        
        <!-- Items -->
        <div class="items">
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr>
                        <td>
                            <div class="item-name">{{ $item->product_name }}</div>
                            @if($item->options)
                                <div class="item-options">
                                    @foreach($item->options as $key => $value)
                                        {{ $value }}@if(!$loop->last), @endif
                                    @endforeach
                                </div>
                            @endif
                            <div class="item-qty">
                                {{ $item->quantity }} x ${{ number_format($item->price, 2) }}
                            </div>
                        </td>
                        <td>${{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Totals -->
        <div class="totals">
            <table>
                <tr>
                    <td class="label">Subtotal</td>
                    <td class="value">${{ number_format($order->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td class="label">Tax</td>
                    <td class="value">${{ number_format($order->tax_amount, 2) }}</td>
                </tr>
                @if($order->discount_amount > 0)
                <tr>
                    <td class="label">Discount</td>
                    <td class="value">-${{ number_format($order->discount_amount, 2) }}</td>
                </tr>
                @endif
                <tr class="total-row">
                    <td>TOTAL</td>
                    <td class="value">${{ number_format($order->total_amount, 2) }}</td>
                </tr>
            </table>
        </div>
        
        <!-- Payment Info -->
        <div class="payment-info">
            <div class="payment-method">{{ strtoupper($order->payment_method) }}</div>
            <div class="payment-status">
                @if($order->payment_status == 'paid')
                    ✓ PAID
                @else
                    {{ strtoupper($order->payment_status) }}
                @endif
            </div>
        </div>
        
        <!-- QR Code -->
        @if($order->verification_code)
        <div class="qr-section">
            <div class="qr-code">
                <img src="data:image/svg+xml;base64,{{ base64_encode($qrCode) }}" alt="QR Code">
            </div>
            <div class="verification-code">{{ $order->verification_code }}</div>
        </div>
        @endif
        
        <!-- Footer -->
        <div class="footer">
            <p class="thank-you">Thank you for your order!</p>
            <p>Please keep this receipt for your records.</p>
            <p>{{ now()->format('M d, Y H:i') }}</p>
        </div>
    </div>
</body>
</html>
