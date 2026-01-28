<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PaymentSetting;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Show Razorpay payment page
     */
    public function razorpay(string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();
        
        // Verify order is pending payment
        if ($order->payment_status !== 'pending') {
            return redirect()->route('order.confirmation', $order)
                ->with('error', 'This order has already been processed.');
        }

        $store = $order->store;
        
        // Get Razorpay credentials from store or system settings
        $razorpayKeyId = $store->razorpay_key_id ?? config('services.razorpay.key');
        
        if (!$razorpayKeyId) {
            return redirect()->route('order.confirmation', $order)
                ->with('error', 'Razorpay is not configured for this store.');
        }

        // Store order ID in session for callback
        session(['payment_order_id' => $order->id]);

        return view('payment.razorpay', [
            'order' => $order,
            'store' => $store,
            'razorpayKeyId' => $razorpayKeyId,
        ]);
    }

    /**
     * Handle Razorpay payment callback
     */
    public function razorpayCallback(Request $request)
    {
        $validated = $request->validate([
            'razorpay_payment_id' => 'required|string',
            'razorpay_order_id' => 'nullable|string',
            'razorpay_signature' => 'nullable|string',
        ]);

        $orderId = session('payment_order_id');
        $order = Order::find($orderId);

        if (!$order) {
            return redirect()->route('home')
                ->with('error', 'Order not found.');
        }

        // In production, verify the signature using Razorpay's SDK
        // $api = new Api($keyId, $keySecret);
        // $api->utility->verifyPaymentSignature($attributes);

        // Mark order as paid
        $order->markAsPaid($validated['razorpay_payment_id']);
        $order->update(['order_status' => 'confirmed']);

        // Clear session
        session()->forget('payment_order_id');

        return redirect()->route('order.confirmation', $order)
            ->with('success', 'Payment successful! Your order has been confirmed.');
    }

    /**
     * Handle Razorpay payment failure
     */
    public function razorpayFailed(Request $request)
    {
        $orderId = session('payment_order_id');
        $order = Order::find($orderId);

        if ($order) {
            $order->update(['payment_status' => 'failed']);
        }

        session()->forget('payment_order_id');

        return redirect()->route('order.confirmation', $order ?? '/')
            ->with('error', 'Payment failed. Please try again or contact support.');
    }

    /**
     * Show Stripe payment page
     */
    public function stripe(string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();
        
        if ($order->payment_status !== 'pending') {
            return redirect()->route('order.confirmation', $order)
                ->with('error', 'This order has already been processed.');
        }

        $store = $order->store;
        
        $stripePublicKey = $store->stripe_public_key ?? config('services.stripe.key');
        
        if (!$stripePublicKey) {
            return redirect()->route('order.confirmation', $order)
                ->with('error', 'Stripe is not configured for this store.');
        }

        session(['payment_order_id' => $order->id]);

        return view('payment.stripe', [
            'order' => $order,
            'store' => $store,
            'stripePublicKey' => $stripePublicKey,
        ]);
    }

    /**
     * Handle Stripe payment callback
     */
    public function stripeCallback(Request $request)
    {
        $validated = $request->validate([
            'payment_intent_id' => 'required|string',
        ]);

        $orderId = session('payment_order_id');
        $order = Order::find($orderId);

        if (!$order) {
            return redirect()->route('home')
                ->with('error', 'Order not found.');
        }

        $order->markAsPaid($validated['payment_intent_id']);
        $order->update(['order_status' => 'confirmed']);

        session()->forget('payment_order_id');

        return redirect()->route('order.confirmation', $order)
            ->with('success', 'Payment successful! Your order has been confirmed.');
    }
}
