<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Store;
use App\Services\QRCodeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected QRCodeService $qrCodeService;

    public function __construct(QRCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
    }

    /**
     * Display a listing of all orders across all stores
     */
    public function index(Request $request)
    {
        $query = Order::with(['store', 'user', 'items']);

        // Search filter
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('order_number', 'like', '%' . $searchTerm . '%')
                    ->orWhere('customer_name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('customer_email', 'like', '%' . $searchTerm . '%')
                    ->orWhereHas('user', function ($uq) use ($searchTerm) {
                        $uq->where('name', 'like', '%' . $searchTerm . '%')
                            ->orWhere('email', 'like', '%' . $searchTerm . '%');
                    });
            });
        }

        // Store filter
        if ($request->filled('store')) {
            $query->where('store_id', $request->store);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('order_status', $request->status);
        }

        // Payment status filter
        if ($request->filled('payment')) {
            $query->where('payment_status', $request->payment);
        }

        // Date filter
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $orders = $query->latest()->paginate(20);
        $stores = Store::orderBy('name')->get();

        // Order stats
        $totalOrders = Order::count();
        $pendingOrders = Order::where('order_status', 'pending')->count();
        $completedOrders = Order::where('order_status', 'completed')->count();

        return view('admin.orders.index', compact(
            'orders',
            'stores',
            'totalOrders',
            'pendingOrders',
            'completedOrders'
        ));
    }

    /**
     * Display order verification scanner
     */
    public function scanner()
    {
        $stores = Store::orderBy('name')->get();

        return view('admin.orders.scanner', compact('stores'));
    }

    /**
     * Display the specified order
     */
    public function show(Order $order)
    {
        $order->load(['customer', 'items.product', 'store']);

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Scan and verify order QR code (Admin can verify any store's orders)
     */
    public function scan(Request $request)
    {
        $validated = $request->validate([
            'qr_data' => 'required|string',
        ]);

        try {
            $payload = json_decode($validated['qr_data'], true);

            if (!$payload || !isset($payload['oid'], $payload['sid'], $payload['token'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid QR code format',
                ], 400);
            }

            // Fetch the order
            $order = Order::with(['customer', 'items.product', 'store'])
                ->where('id', $payload['oid'])
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found',
                ], 404);
            }

            // Security: Verify token matches
            if ($order->verification_code !== $payload['token']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid verification token - QR code may be tampered',
                ], 403);
            }

            // Check if order is completed
            if ($order->order_status === 'completed') {
                return response()->json([
                    'success' => true,
                    'message' => 'This order has already been completed',
                    'order' => $this->formatOrderResponse($order),
                    'already_completed' => true,
                ]);
            }

            // Check if order is cancelled
            if ($order->order_status === 'cancelled') {
                return response()->json([
                    'success' => false,
                    'message' => 'This order has been cancelled',
                    'order' => $this->formatOrderResponse($order),
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Order verified successfully',
                'order' => $this->formatOrderResponse($order),
                'already_completed' => false,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to parse QR code data',
            ], 400);
        }
    }

    /**
     * Update order status (Admin can update any order)
     */
    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'order_status' => 'required|in:pending,confirmed,processing,completed,cancelled',
        ]);

        // If cancelling, restore stock
        if ($validated['order_status'] === 'cancelled' && $order->order_status !== 'cancelled') {
            foreach ($order->items as $item) {
                if ($item->product) {
                    $item->product->restoreStock($item->quantity);
                }
            }
        }

        $order->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully',
                'order' => $this->formatOrderResponse($order->fresh(['customer', 'items.product', 'store'])),
            ]);
        }

        return back()->with('success', 'Order status updated successfully.');
    }

    /**
     * Mark order as paid (Admin can mark any order as paid)
     */
    public function markPaid(Request $request, Order $order)
    {
        if ($order->payment_status === 'paid') {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order is already marked as paid.',
                ], 400);
            }
            return back()->with('error', 'Order is already marked as paid.');
        }

        if ($order->order_status === 'cancelled') {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot mark a cancelled order as paid.',
                ], 400);
            }
            return back()->with('error', 'Cannot mark a cancelled order as paid.');
        }

        $order->markAsPaid('ADMIN-' . now()->format('YmdHis'));
        $order->update(['order_status' => 'confirmed']);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Order marked as paid successfully.',
                'order' => $this->formatOrderResponse($order->fresh(['customer', 'items.product', 'store'])),
            ]);
        }

        return back()->with('success', 'Order marked as paid successfully.');
    }

    /**
     * Complete an order
     */
    public function completeOrder(Request $request, Order $order)
    {
        if ($order->order_status === 'completed') {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order is already completed.',
                ], 400);
            }
            return back()->with('error', 'Order is already completed.');
        }

        if ($order->order_status === 'cancelled') {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot complete a cancelled order.',
                ], 400);
            }
            return back()->with('error', 'Cannot complete a cancelled order.');
        }

        $order->update(['order_status' => 'completed']);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Order completed successfully.',
                'order' => $this->formatOrderResponse($order->fresh(['customer', 'items.product', 'store'])),
            ]);
        }

        return back()->with('success', 'Order completed successfully.');
    }

    /**
     * Download receipt as PDF
     */
    public function receipt(Order $order)
    {
        $order->load(['items.product', 'store', 'customer']);

        $pdf = Pdf::loadView('orders.receipt', compact('order'));

        return $pdf->download("receipt-{$order->order_number}.pdf");
    }

    /**
     * Format order response for JSON
     */
    private function formatOrderResponse(Order $order): array
    {
        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'store_name' => $order->store ? $order->store->name : 'Unknown Store',
            'store_id' => $order->store_id,
            'customer_name' => $order->customer ? $order->customer->name : 'Walk-in Customer',
            'customer_email' => $order->customer ? $order->customer->email : null,
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
        ];
    }
}
