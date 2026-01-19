<?php

namespace App\Http\Controllers\StoreOwner;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of the orders
     */
    public function index(Request $request)
    {
        $store = auth()->user()->store;
        $query = $store->orders()->with('customer');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('order_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('customer', function ($cq) use ($request) {
                      $cq->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        if ($request->filled('order_status')) {
            $query->where('order_status', $request->order_status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $orders = $query->latest()->paginate(15);

        return view('store-owner.orders.index', compact('orders'));
    }

    /**
     * Display the specified order
     */
    public function show(Order $order)
    {
        $this->authorizeOrder($order);

        $order->load(['customer', 'items.product']);

        return view('store-owner.orders.show', compact('order'));
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, Order $order)
    {
        $this->authorizeOrder($order);

        $validated = $request->validate([
            'order_status' => 'required|in:pending,confirmed,processing,completed,cancelled',
        ]);

        // If cancelling, restore stock
        if ($validated['order_status'] === 'cancelled' && $order->order_status !== 'cancelled') {
            foreach ($order->items as $item) {
                $item->product->restoreStock($item->quantity);
            }
        }

        $order->update($validated);

        return back()->with('success', 'Order status updated successfully.');
    }

    /**
     * Download receipt as PDF
     */
    public function receipt(Order $order)
    {
        $this->authorizeOrder($order);

        $order->load(['items.product', 'store', 'customer']);

        $pdf = Pdf::loadView('orders.receipt', compact('order'));

        return $pdf->download("receipt-{$order->order_number}.pdf");
    }

    /**
     * Authorize that the order belongs to the current store
     */
    private function authorizeOrder(Order $order): void
    {
        if ($order->store_id !== auth()->user()->store->id) {
            abort(403);
        }
    }
}
