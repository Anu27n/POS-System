<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$orders = App\Models\Order::all();
echo "=== Orders ===\n";
foreach($orders as $o) {
    echo "ID: {$o->id} | Order#: {$o->order_number} | Total: {$o->total} | Subtotal: {$o->subtotal} | Payment: {$o->payment_status}\n";
}

echo "\n=== Order Items ===\n";
$items = App\Models\OrderItem::all();
foreach($items as $item) {
    echo "Order ID: {$item->order_id} | Product: {$item->product_name} | Price: {$item->price} | Qty: {$item->quantity} | Total: {$item->total}\n";
}
