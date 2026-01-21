<?php

namespace App\Http\Controllers\StoreOwner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PaymentSettingsController extends Controller
{
    /**
     * Display payment settings
     */
    public function index()
    {
        $store = auth()->user()->getEffectiveStore();

        return view('store-owner.settings.payment', compact('store'));
    }

    /**
     * Update payment settings
     */
    public function update(Request $request)
    {
        $store = auth()->user()->getEffectiveStore();

        $validated = $request->validate([
            'enable_online_payment' => 'boolean',
            'enable_counter_payment' => 'boolean',
            'razorpay_enabled' => 'boolean',
            'razorpay_key_id' => 'nullable|string|max:255',
            'razorpay_key_secret' => 'nullable|string|max:255',
            'stripe_enabled' => 'boolean',
            'stripe_publishable_key' => 'nullable|string|max:255',
            'stripe_secret_key' => 'nullable|string|max:255',
            'is_test_mode' => 'boolean',
        ]);

        // Ensure at least one payment method is enabled
        if (!$request->boolean('enable_online_payment') && !$request->boolean('enable_counter_payment')) {
            return back()->with('error', 'At least one payment method must be enabled.');
        }

        $store->update([
            'enable_online_payment' => $request->boolean('enable_online_payment'),
            'enable_counter_payment' => $request->boolean('enable_counter_payment'),
            'razorpay_enabled' => $request->boolean('razorpay_enabled'),
            'razorpay_key_id' => $validated['razorpay_key_id'] ?? null,
            'razorpay_key_secret' => $validated['razorpay_key_secret'] ?? $store->razorpay_key_secret,
            'stripe_enabled' => $request->boolean('stripe_enabled'),
            'stripe_publishable_key' => $validated['stripe_publishable_key'] ?? null,
            'stripe_secret_key' => $validated['stripe_secret_key'] ?? $store->stripe_secret_key,
            'is_test_mode' => $request->boolean('is_test_mode'),
        ]);

        return back()->with('success', 'Payment settings updated successfully.');
    }
}
