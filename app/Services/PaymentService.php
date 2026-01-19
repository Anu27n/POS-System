<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentSetting;
use Illuminate\Http\RedirectResponse;

class PaymentService
{
    /**
     * Initiate payment based on active gateway
     */
    public function initiatePayment(Order $order): RedirectResponse
    {
        $gateway = PaymentSetting::getActiveGateway();

        if (!$gateway) {
            // No payment gateway configured, fall back to counter payment
            $order->update(['payment_method' => 'counter']);
            return redirect()->route('order.confirmation', $order->order_number)
                ->with('info', 'Online payment is not available. Please pay at counter.');
        }

        return match ($gateway->gateway) {
            'razorpay' => $this->initiateRazorpay($order, $gateway),
            'stripe' => $this->initiateStripe($order, $gateway),
            'paypal' => $this->initiatePaypal($order, $gateway),
            default => redirect()->route('order.confirmation', $order->order_number)
                ->with('error', 'Payment gateway not configured.'),
        };
    }

    /**
     * Handle payment callback
     */
    public function handleCallback(string $gateway, array $data): bool
    {
        return match ($gateway) {
            'razorpay' => $this->handleRazorpayCallback($data),
            'stripe' => $this->handleStripeCallback($data),
            'paypal' => $this->handlePaypalCallback($data),
            default => false,
        };
    }

    /**
     * Initiate Razorpay payment
     */
    private function initiateRazorpay(Order $order, PaymentSetting $gateway): RedirectResponse
    {
        // Store order ID in session for callback
        session(['payment_order_id' => $order->id]);

        return redirect()->route('payment.razorpay', $order->order_number);
    }

    /**
     * Initiate Stripe payment
     */
    private function initiateStripe(Order $order, PaymentSetting $gateway): RedirectResponse
    {
        session(['payment_order_id' => $order->id]);

        return redirect()->route('payment.stripe', $order->order_number);
    }

    /**
     * Initiate PayPal payment
     */
    private function initiatePaypal(Order $order, PaymentSetting $gateway): RedirectResponse
    {
        session(['payment_order_id' => $order->id]);

        return redirect()->route('payment.paypal', $order->order_number);
    }

    /**
     * Handle Razorpay callback
     */
    private function handleRazorpayCallback(array $data): bool
    {
        $orderId = session('payment_order_id');
        $order = Order::find($orderId);

        if (!$order) {
            return false;
        }

        // Verify payment signature here in production
        // For now, mark as paid
        $order->markAsPaid($data['razorpay_payment_id'] ?? null);
        $order->update(['order_status' => 'confirmed']);

        session()->forget('payment_order_id');

        return true;
    }

    /**
     * Handle Stripe callback
     */
    private function handleStripeCallback(array $data): bool
    {
        $orderId = session('payment_order_id');
        $order = Order::find($orderId);

        if (!$order) {
            return false;
        }

        $order->markAsPaid($data['payment_intent'] ?? null);
        $order->update(['order_status' => 'confirmed']);

        session()->forget('payment_order_id');

        return true;
    }

    /**
     * Handle PayPal callback
     */
    private function handlePaypalCallback(array $data): bool
    {
        $orderId = session('payment_order_id');
        $order = Order::find($orderId);

        if (!$order) {
            return false;
        }

        $order->markAsPaid($data['transaction_id'] ?? null);
        $order->update(['order_status' => 'confirmed']);

        session()->forget('payment_order_id');

        return true;
    }
}
