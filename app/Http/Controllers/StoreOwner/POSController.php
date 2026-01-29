<?php

namespace App\Http\Controllers\StoreOwner;

use App\Http\Controllers\Controller;
use App\Models\CashRegisterSession;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderTax;
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
        $store = auth()->user()->getEffectiveStore();
        $user = auth()->user();

        // Check for cash register session
        $cashRegisterSession = CashRegisterSession::getAnyOpenSession($store->id);
        $lastClosedSession = CashRegisterSession::getLastClosedSession($store->id);
        $suggestedOpeningCash = $lastClosedSession ? $lastClosedSession->closing_cash : 0;

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

        // Get tax settings
        $taxSettings = $store->taxSettings;
        $taxes = ($taxSettings && $taxSettings->taxes_enabled) ? $store->enabledTaxes : collect();
        $taxRate = $store->tax_rate ?? 0; // Legacy tax rate

        return view('store-owner.pos.index', compact(
            'store',
            'pendingOrders',
            'products',
            'categories',
            'taxRate',
            'taxes',
            'taxSettings',
            'cashRegisterSession',
            'suggestedOpeningCash'
        ));
    }

    /**
     * Process a POS order (direct sale)
     */
    public function process(Request $request)
    {
        $store = auth()->user()->getEffectiveStore();

        // Parse request data
        $data = $request->json()->all();
        $items = $data['items'] ?? [];
        $paymentMethod = $data['payment_method'] ?? 'cash';
        $notes = $data['notes'] ?? null;
        $discountAmount = floatval($data['discount_amount'] ?? 0);
        $customerId = $data['customer_id'] ?? null;

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

            // Calculate taxes using new tax system
            $taxSettings = $store->taxSettings;
            $taxableAmount = $subtotal - $discountAmount;
            $totalTax = 0;
            $taxBreakdown = [];

            if ($taxSettings && $taxSettings->taxes_enabled) {
                foreach ($store->enabledTaxes as $tax) {
                    $taxAmount = $tax->calculateTax($taxableAmount);
                    $totalTax += $taxAmount;
                    $taxBreakdown[] = [
                        'store_tax_id' => $tax->id,
                        'tax_name' => $tax->name,
                        'tax_percentage' => $tax->percentage,
                        'taxable_amount' => $taxableAmount,
                        'tax_amount' => $taxAmount,
                    ];
                }
            } else {
                // Legacy tax rate
                $taxRate = $store->tax_rate ?? 0;
                $totalTax = $taxableAmount * ($taxRate / 100);
            }

            $total = $subtotal - $discountAmount + $totalTax;

            // Create order with optional store customer
            $order = Order::create([
                'store_id' => $store->id,
                'store_customer_id' => $customerId ?: null,
                'subtotal' => $subtotal,
                'tax' => $totalTax,
                'discount' => $discountAmount,
                'total' => $total,
                'payment_method' => $paymentMethod,
                'payment_status' => 'paid',
                'order_status' => 'completed',
                'notes' => $notes,
                'paid_at' => now(),
                'transaction_id' => 'POS-' . now()->format('YmdHis') . '-' . rand(1000, 9999),
            ]);

            // Create order tax records
            foreach ($taxBreakdown as $taxRecord) {
                OrderTax::create(array_merge($taxRecord, ['order_id' => $order->id]));
            }

            // Create order items
            foreach ($orderItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product']->id,
                    'product_name' => $item['product']->name,
                    'product_sku' => $item['product']->sku ?? '',
                    'price' => $item['product']->price,
                    'quantity' => $item['quantity'],
                    'total' => $item['subtotal'],
                ]);

                // Reduce stock
                $item['product']->reduceStock($item['quantity']);
            }

            // Generate verification QR code and save to storage
            $qrPath = $this->qrCodeService->generateAndSaveOrderQR($order);
            $order->update(['verification_qr_path' => $qrPath]);

            // Update customer stats if a store customer was selected
            if ($customerId) {
                $storeCustomer = $store->customers()->find($customerId);
                if ($storeCustomer) {
                    $storeCustomer->recordOrder($total);
                }
            }

            // Add transaction to cash register if session is open
            $cashSession = CashRegisterSession::getAnyOpenSession($store->id);
            if ($cashSession) {
                $cashSession->addTransaction('sale', $paymentMethod, $total, $order->id);
            }

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

        $store = auth()->user()->getEffectiveStore();

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
     * Lookup order by order number (manual entry)
     */
    public function lookupOrder(Request $request)
    {
        $validated = $request->validate([
            'order_number' => 'required|string',
        ]);

        $store = auth()->user()->getEffectiveStore();
        
        // Clean the order number (remove ORD prefix if entered, handle hyphen)
        $orderNumber = strtoupper(trim($validated['order_number']));
        
        // Remove 'ORD' or 'ORD-' prefix if user entered it
        $orderNumber = preg_replace('/^ORD-?/', '', $orderNumber);
        
        // Add the standard ORD- prefix
        $orderNumber = 'ORD-' . $orderNumber;

        // Find the order
        $order = Order::with(['customer', 'items.product'])
            ->where('store_id', $store->id)
            ->where('order_number', $orderNumber)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found. Please check the order number.',
            ], 404);
        }

        // Check if order is already completed
        if ($order->order_status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'This order has already been completed.',
                'order' => [
                    'order_number' => $order->order_number,
                    'order_status' => $order->order_status,
                ],
            ]);
        }

        // Check if order is cancelled
        if ($order->order_status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'This order has been cancelled.',
                'order' => [
                    'order_number' => $order->order_number,
                    'order_status' => $order->order_status,
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order found',
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
        $store = auth()->user()->getEffectiveStore();

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

        // Add transaction to cash register if session is open
        $cashSession = CashRegisterSession::getAnyOpenSession($store->id);
        if ($cashSession) {
            $cashSession->addTransaction('sale', $paymentMethod, $order->total, $order->id);
        }

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
        $store = auth()->user()->getEffectiveStore();

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
        $store = auth()->user()->getEffectiveStore();

        if ($order->store_id !== $store->id) {
            abort(403);
        }

        $order->load(['items.product', 'store', 'customer']);

        $pdf = Pdf::loadView('orders.receipt', compact('order'));

        return $pdf->download("receipt-{$order->order_number}.pdf");
    }

    /**
     * Search customers for POS
     */
    public function searchCustomers(Request $request)
    {
        try {
            $store = auth()->user()->getEffectiveStore();
            
            if (!$store) {
                return response()->json(['customers' => [], 'error' => 'No store found']);
            }
            
            $search = $request->input('q', '');

            if (strlen($search) < 2) {
                return response()->json(['customers' => []]);
            }

            $customers = $store->customers()
                ->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })
                ->select('id', 'name', 'phone', 'email')
                ->limit(10)
                ->get();

            return response()->json(['customers' => $customers]);
        } catch (\Exception $e) {
            \Log::error('Customer search error: ' . $e->getMessage());
            return response()->json(['customers' => [], 'error' => $e->getMessage()]);
        }
    }

    /**
     * Create a new customer from POS
     */
    public function createCustomer(Request $request)
    {
        try {
            $store = auth()->user()->getEffectiveStore();
            
            if (!$store) {
                return response()->json([
                    'success' => false,
                    'message' => 'No store found',
                ], 400);
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
            ]);

            // Check if customer with same phone already exists (only if phone provided)
            if (!empty($validated['phone'])) {
                $existing = $store->customers()->where('phone', $validated['phone'])->first();
                if ($existing) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Customer already exists with this phone number.',
                        'customer' => $existing,
                    ]);
                }
            }

            $customer = $store->customers()->create([
                'name' => $validated['name'],
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['email'] ?? null,
                'is_manually_added' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Customer created successfully.',
                'customer' => $customer,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error: ' . implode(', ', $e->validator->errors()->all()),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Customer create error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error creating customer: ' . $e->getMessage(),
            ], 500);
        }
    }
}
