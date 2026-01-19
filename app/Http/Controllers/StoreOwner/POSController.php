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
            ->where('payment_status', 'pending')
            ->where('payment_method', 'counter')
            ->latest()
            ->get();

        // Get all active products for quick add
        $products = $store->products()
            ->where('is_active', true)
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

            // Generate verification QR code
            $qrCode = $this->qrCodeService->generateOrderQR($order);
            $order->update(['verification_qr' => $qrCode]);

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

    /**
     * Scan and verify order QR code
     */
    public function scan(Request $request)
    {
        $validated = $request->validate([
            'verification_code' => 'required|string',
        ]);

        $store = auth()->user()->store;

        $order = Order::where('verification_code', $validated['verification_code'])
            ->where('store_id', $store->id)
            ->with(['customer', 'items.product'])
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found or does not belong to this store.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'order' => $order,
        ]);
    }

    /**
     * Mark order as paid (for counter payments)
     */
    public function markPaid(Request $request, Order $order)
    {
        $store = auth()->user()->store;

        if ($order->store_id !== $store->id) {
            abort(403);
        }

        if ($order->payment_status === 'paid') {
            return back()->with('info', 'Order is already paid.');
        }

        $order->markAsPaid('COUNTER-' . now()->format('YmdHis'));
        $order->update(['order_status' => 'completed']);

        return back()->with('success', 'Order marked as paid successfully.');
    }

    /**
     * Get order details by verification code
     */
    public function getOrder(string $verificationCode)
    {
        $store = auth()->user()->store;

        $order = Order::where('verification_code', $verificationCode)
            ->where('store_id', $store->id)
            ->with(['customer', 'items.product'])
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'order' => $order,
        ]);
    }
}
