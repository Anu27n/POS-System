<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Store;
use App\Services\PaymentService;
use App\Services\QRCodeService;
use Illuminate\Http\Request;
use App\Models\StoreCustomer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CheckoutController extends Controller
{
    protected PaymentService $paymentService;
    protected QRCodeService $qrCodeService;

    public function __construct(PaymentService $paymentService, QRCodeService $qrCodeService)
    {
        $this->paymentService = $paymentService;
        $this->qrCodeService = $qrCodeService;
    }

    /**
     * Show checkout page
     */
    public function index()
    {
        $cart = $this->getCart();

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Your cart is empty.');
        }

        $cart->load(['items.product', 'store']);
        $store = $cart->store;

        // Calculate totals
        $subtotal = $cart->subtotal;
        $taxRate = $store->tax_rate ?? 0;
        $tax = $subtotal * ($taxRate / 100);
        $total = $subtotal + $tax;

        // Get available payment methods
        $paymentMethods = $store->getAvailablePaymentMethods();

        // Check if user is logged in
        $isLoggedIn = auth()->check();

        return view('checkout.index', compact('cart', 'store', 'subtotal', 'tax', 'total', 'paymentMethods', 'isLoggedIn'));
    }

    /**
     * Process checkout
     */
    public function process(Request $request)
    {
        // Check if user is logged in
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Please login to continue with checkout.',
                'require_login' => true,
            ], 401);
        }

        $cart = $this->getCart();

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Your cart is empty.');
        }

        $cart->load(['items.product', 'store']);
        $store = $cart->store;

        // Validate payment method against store settings
        $availableMethods = array_keys($store->getAvailablePaymentMethods());

        $validated = $request->validate([
            'payment_method' => 'required|in:' . implode(',', $availableMethods),
            'notes' => 'nullable|string|max:500',
        ]);

        // Validate stock availability
        foreach ($cart->items as $item) {
            if ($item->product->track_inventory && $item->product->stock_quantity < $item->quantity) {
                return back()->with('error', "Not enough stock for {$item->product->name}.");
            }
        }

        try {
            DB::beginTransaction();

            // Calculate totals
            $subtotal = $cart->subtotal;
            $taxRate = $store->tax_rate ?? 0;
            $tax = $subtotal * ($taxRate / 100);
            $total = $subtotal + $tax;

            // Create or update store customer record
            $storeCustomer = StoreCustomer::findOrCreateFromUser($store, auth()->user());

            // Create order (order_number and verification_code auto-generated in model)
            $order = Order::create([
                'user_id' => auth()->id(),
                'store_id' => $store->id,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'discount' => 0,
                'total' => $total,
                'payment_method' => $validated['payment_method'],
                'payment_status' => 'pending',
                'order_status' => 'pending',
                'notes' => $validated['notes'] ?? null,
            ]);

            // Create order items and reduce stock
            foreach ($cart->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'product_sku' => $item->product->sku ?? '',
                    'price' => $item->product->price,
                    'quantity' => $item->quantity,
                    'total' => $item->product->price * $item->quantity,
                ]);

                // Reduce stock
                $item->product->reduceStock($item->quantity);
            }

            // Generate verification QR code image and save to storage
            $qrPath = $this->qrCodeService->generateAndSaveOrderQR($order);
            $order->update(['verification_qr_path' => $qrPath]);

            // Update customer stats
            $storeCustomer->recordOrder($total);

            // Clear cart
            $cart->items()->delete();
            $cart->delete();

            DB::commit();

            // If online payment, redirect to payment gateway
            if ($validated['payment_method'] === 'online') {
                return $this->paymentService->initiatePayment($order);
            }

            // For counter payment, show order confirmation
            return redirect()->route('order.confirmation', $order)
                ->with('success', 'Order placed successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to process order: ' . $e->getMessage());
        }
    }

    /**
     * Show order confirmation
     */
    public function confirmation(Order $order)
    {
        // Ensure user can only see their own orders
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $order->load(['store', 'items.product']);

        return view('orders.confirmation', compact('order'));
    }

    /**
     * Get the current user's cart
     */
    private function getCart(): ?Cart
    {
        if (auth()->check()) {
            return Cart::where('user_id', auth()->id())
                ->with(['items.product', 'store'])
                ->first();
        }

        $sessionId = session()->getId();
        return Cart::where('session_id', $sessionId)
            ->with(['items.product', 'store'])
            ->first();
    }
}
