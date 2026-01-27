<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Order Received</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 10px 0 0;
            opacity: 0.9;
        }
        .content {
            padding: 30px;
        }
        .order-badge {
            display: inline-block;
            background: #10b981;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            margin-bottom: 20px;
        }
        .order-details {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .order-details h3 {
            margin: 0 0 15px;
            color: #333;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .items-table th, .items-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        .items-table th {
            background: #f8f9fa;
            font-weight: 600;
        }
        .total-row {
            font-weight: bold;
            font-size: 18px;
            background: #e8f5e9;
        }
        .btn {
            display: inline-block;
            background: #0d6efd;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin-top: 20px;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 14px;
            background: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>🛒 New Order Received!</h1>
            <p>{{ $store->name }}</p>
        </div>
        
        <div class="content">
            <span class="order-badge">Order #{{ $order->order_number }}</span>
            
            <div class="order-details">
                <h3>Order Summary</h3>
                <div class="detail-row">
                    <span>Order Number:</span>
                    <strong>{{ $order->order_number }}</strong>
                </div>
                <div class="detail-row">
                    <span>Date:</span>
                    <span>{{ $order->created_at->format('M d, Y H:i') }}</span>
                </div>
                <div class="detail-row">
                    <span>Payment Method:</span>
                    <span>{{ ucfirst($order->payment_method) }}</span>
                </div>
                <div class="detail-row">
                    <span>Payment Status:</span>
                    <span style="color: {{ $order->payment_status === 'paid' ? '#10b981' : '#f59e0b' }}">
                        {{ ucfirst($order->payment_status) }}
                    </span>
                </div>
                @if($order->customer)
                <div class="detail-row">
                    <span>Customer:</span>
                    <span>{{ $order->customer->name }}</span>
                </div>
                @endif
            </div>
            
            <h3>Order Items</h3>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr>
                        <td>{{ $item->product_name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ \App\Helpers\CurrencyHelper::format($item->subtotal, $store->currency ?? 'INR') }}</td>
                    </tr>
                    @endforeach
                    <tr>
                        <td colspan="2">Subtotal</td>
                        <td>{{ \App\Helpers\CurrencyHelper::format($order->subtotal, $store->currency ?? 'INR') }}</td>
                    </tr>
                    @if($order->tax > 0)
                    <tr>
                        <td colspan="2">Tax</td>
                        <td>{{ \App\Helpers\CurrencyHelper::format($order->tax, $store->currency ?? 'INR') }}</td>
                    </tr>
                    @endif
                    @if($order->discount > 0)
                    <tr>
                        <td colspan="2">Discount</td>
                        <td>-{{ \App\Helpers\CurrencyHelper::format($order->discount, $store->currency ?? 'INR') }}</td>
                    </tr>
                    @endif
                    <tr class="total-row">
                        <td colspan="2">Total</td>
                        <td>{{ \App\Helpers\CurrencyHelper::format($order->total, $store->currency ?? 'INR') }}</td>
                    </tr>
                </tbody>
            </table>
            
            <center>
                <a href="{{ route('store-owner.orders.show', $order) }}" class="btn">View Order Details</a>
            </center>
        </div>
        
        <div class="footer">
            <p>This is an automated notification from {{ config('app.name') }}.</p>
            <p>© {{ date('Y') }} {{ $store->name }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
