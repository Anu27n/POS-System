<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Status Updated</title>
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
        .content {
            padding: 30px;
            text-align: center;
        }
        .status-change {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            margin: 30px 0;
            flex-wrap: wrap;
        }
        .status-badge {
            padding: 12px 24px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 16px;
        }
        .status-old {
            background: #e9ecef;
            color: #6c757d;
            text-decoration: line-through;
        }
        .status-new {
            background: #10b981;
            color: white;
        }
        .status-arrow {
            font-size: 24px;
            color: #6c757d;
        }
        .order-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: left;
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
            <h1>📦 Order Status Updated</h1>
        </div>
        
        <div class="content">
            <h2>Order #{{ $order->order_number }}</h2>
            
            <div class="status-change">
                <span class="status-badge status-old">{{ ucfirst($previousStatus) }}</span>
                <span class="status-arrow">→</span>
                <span class="status-badge status-new">{{ ucfirst($order->order_status) }}</span>
            </div>
            
            <div class="order-info">
                <p><strong>Store:</strong> {{ $store->name }}</p>
                <p><strong>Total:</strong> {{ \App\Helpers\CurrencyHelper::format($order->total, $store->currency ?? 'INR') }}</p>
                <p><strong>Payment Status:</strong> {{ ucfirst($order->payment_status) }}</p>
            </div>
            
            @if($order->order_status === 'completed')
            <p style="font-size: 18px; color: #10b981;">
                ✅ Your order has been completed. Thank you for shopping with us!
            </p>
            @elseif($order->order_status === 'processing')
            <p style="font-size: 18px;">
                🔄 Your order is being prepared.
            </p>
            @elseif($order->order_status === 'confirmed')
            <p style="font-size: 18px;">
                ✓ Your order has been confirmed and will be processed soon.
            </p>
            @endif
            
            <a href="{{ route('orders.show', $order) }}" class="btn">Track Your Order</a>
        </div>
        
        <div class="footer">
            <p>This is an automated notification from {{ config('app.name') }}.</p>
            <p>© {{ date('Y') }} {{ $store->name }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
