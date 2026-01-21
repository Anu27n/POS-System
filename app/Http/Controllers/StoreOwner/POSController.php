<?php

namespace App\Http\Controllers\StoreOwner;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\QRCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class POSController extends Controller
{
    protected QRCodeService $qrCodeService;

    public function __construct(QRCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
    }

    /**
     * Display the POS panel
     */
    public function index()
    {
        $store = auth()->user()->store;

        $pendingOrders = $store->orders()
            ->with('customer')
            ->whereIn('order_status', ['pending', 'confirmed', 'processing'])
            ->latest()
            ->take(20)
            ->get();

        // Get all active products for quick add
        $products = $store->products()
            ->where('status', 'available')
            ->orderBy('name')
            ->get();

        $categories = $store->categories()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $taxRate = $store->tax_rate ?? 0;

        return view('store-owner.pos.index', compact('store', 'pendingOrders', 'products', 'categories', 'taxRate'));
    }

    /**
     * Process a POS order (direct sale)
     */
    public function process(Request $request)
    {
        $store = auth()->user()->store;

        // Parse request data
        $data = $request->json()->all();
        $items = $data['items'] ?? [];
        $paymentMethod = $data['payment_method'] ?? 'cash';
        $notes = $data['notes'] ?? null;
        $discountAmount = floatval($data['discount_amount'] ?? 0);

        if (empty($items)) {
            return response()->json([
                'success' => false,
                'message' => 'No items in cart.',
            ], 422);
        }

        DB::beginTransaction();

        try {
            $subtotal = 0;
            $orderItems = [];

            // Validate and prepare items
            foreach ($items as $item) {
                $productId = $item['productId'] ?? $item['product_id'] ?? null;
                $quantity = intval($item['quantity'] ?? 1);

                if (!$productId) {
                    throw new \Exception("Invalid product in cart");
                }

                $product = Product::where('id', $productId)
                    ->where('store_id', $store->id)
                    ->firstOrFail();

                // Check stock
                if ($product->track_inventory && $product->stock_quantity < $quantity) {
                    throw new \Exception("Not enough stock for {$product->name}");
                }

                $itemTotal = $product->price * $quantity;
                $subtotal += $itemTotal;

                $orderItems[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'subtotal' => $itemTotal,
                ];
            }

            // Calculate totals
            $taxRate = $store->tax_rate ?? 0;
            $tax = ($subtotal - $discountAmount) * ($taxRate / 100);
            $total = $subtotal - $discountAmount + $tax;

            // Create order
            $order = Order::create([
                'user_id' => null, // Walk-in customer
                'store_id' => $store->id,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'discount' => $discountAmount,
                'total' => $total,
                'payment_method' => $paymentMethod,
                'payment_status' => 'paid',
                'order_status' => 'completed',
                'notes' => $notes,
                'paid_at' => now(),
                'transaction_id' => 'POS-' . now()->format('YmdHis') . '-' . rand(1000, 9999),
            ]);

            // Create order items
            foreach ($orderItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product']->id,
                    'product_name' => $item['product']->name,
                    'product_sku' => $item['product']->sku ?? '',
                    'price' => $item['product']->price,
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['subtotal'],
                ]);

                // Reduce stock
                $item['product']->reduceStock($item['quantity']);
            }

            // Generate verification QR code and save to storage
            $qrPath = $this->qrCodeService->generateAndSaveOrderQR($order);
            $order->update(['verification_qr_path' => $qrPath]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order processed successfully!',
                'order_number' => $order->order_number,
                'receipt_url' => route('store-owner.orders.receipt', $order),
                'order' => $order->load('items.product'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Scan and verify order QR code with full security validation
     */
    public function scan(Request $request)
    {
        $validated = $request->validate([
            'qr_data' => 'required|string',
        ]);

        $store = auth()->user()->store;

        // Use QRCodeService to validate the scanned QR data
        $result = $this->qrCodeService->verifyOrderQR($validated['qr_data'], $store->id);

        if (!$result['valid']) {
            return response()->json([
                'success' => false,
                'message' => $result['error'],
                'order' => $result['order'] ? [
                    'order_number' => $result['order']->order_number,
                    'order_status' => $result['order']->order_status,
                ] : null,
            ], $result['order'] ? 200 : 404);
        }

        $order = $result['order'];

        return response()->json([
            'success' => true,
            'message' => 'Order verified successfully',
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->customer ? $order->customer->name : 'Walk-in Customer',
                'items' => $order->items->map(fn($item) => [
                    'name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'subtotal' => $item->subtotal,
                ]),
                'subtotal' => $order->subtotal,
                'tax' => $order->tax,
                'discount' => $order->discount,
                'total' => $order->total,
                'payment_method' => $order->payment_method,
                'payment_status' => $order->payment_status,
                'order_status' => $order->order_status,
                'created_at' => $order->created_at->format('M d, Y H:i'),
            ],
        ]);
    }

    /**
     * Mark order as paid (for counter payments)
     */
    public function markPaid(Request $request, Order $order)
    {
        $store = auth()->user()->store;

        // Security: Verify store ownership
        if ($order->store_id !== $store->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: This order belongs to a different store.',
            ], 403);
        }

        // Check if already paid
        if ($order->payment_status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Order is already marked as paid.',
            ], 400);
        }

        // Check if order is cancelled
        if ($order->order_status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot mark a cancelled order as paid.',
            ], 400);
        }

        // Get payment method from request
        $paymentMethod = $request->input('payment_method', 'cash');
        $validMethods = ['cash', 'card', 'upi'];
        
        if (!in_array($paymentMethod, $validMethods)) {
            $paymentMethod = 'cash';
        }

        $transactionId = strtoupper($paymentMethod) . '-COUNTER-' . now()->format('YmdHis');
        
        $order->update([
            'payment_method' => $paymentMethod,
            'payment_status' => 'paid',
            'order_status' => 'confirmed',
            'paid_at' => now(),
            'transaction_id' => $transactionId,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Order marked as paid successfully.',
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'payment_method' => $order->payment_method,
                'payment_status' => $order->payment_status,
                'order_status' => $order->order_status,
                'receipt_url' => route('store-owner.orders.receipt', $order),
            ],
        ]);
    }

    /**
     * Complete an order (final step - order cannot be scanned again)
     */
    public function completeOrder(Request $request, Order $order)
    {
        $store = auth()->user()->store;

        // Security: Verify store ownership
        if ($order->store_id !== $store->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: This order belongs to a different store.',
            ], 403);
        }

        // Check if already completed
        if ($order->order_status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Order is already completed.',
            ], 400);
        }

        // Check if cancelled
        if ($order->order_status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot complete a cancelled order.',
            ], 400);
        }

        $order->update(['order_status' => 'completed']);

        return response()->json([
            'success' => true,
            'message' => 'Order completed successfully. This order cannot be scanned again.',
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'payment_status' => $order->payment_status,
                'order_status' => $order->order_status,
            ],
        ]);
    }

    /**
     * Generate receipt PDF for an order
     */
    public function receipt(Order $order)
    {
        $store = auth()->user()->store;

        if ($order->store_id !== $store->id) {
            abort(403);
        }

        $order->load(['items.product', 'store', 'customer']);

        $pdf = Pdf::loadView('orders.receipt', compact('order'));

        return $pdf->download("receipt-{$order->order_number}.pdf");
    }
}
