<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display user's orders
     */
    public function index()
    {
        $orders = Order::where('user_id', auth()->id())
            ->with('store')
            ->latest()
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    /**
     * Display order details
     */
    public function show(string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', auth()->id())
            ->with(['store', 'items.product'])
            ->firstOrFail();

        return view('orders.show', compact('order'));
    }

    /**
     * Download receipt as PDF
     */
    public function receipt(string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', auth()->id())
            ->with(['store', 'items.product', 'customer'])
            ->firstOrFail();

        $pdf = Pdf::loadView('orders.receipt', compact('order'));

        return $pdf->download('receipt-' . $order->order_number . '.pdf');
    }

    /**
     * Cancel order
     */
    public function cancel(string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if ($order->order_status !== 'pending') {
            return back()->with('error', 'Only pending orders can be cancelled.');
        }

        if ($order->payment_status === 'paid') {
            return back()->with('error', 'Paid orders cannot be cancelled. Please contact the store.');
        }

        $order->cancel();

        return back()->with('success', 'Order cancelled successfully.');
    }
}
