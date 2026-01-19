<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentSetting;
use Illuminate\Http\Request;

class PaymentSettingController extends Controller
{
    /**
     * Display payment settings
     */
    public function index()
    {
        $gateways = PaymentSetting::all()->keyBy('gateway');

        // Ensure all gateways exist
        $defaultGateways = ['razorpay', 'stripe', 'paypal'];
        foreach ($defaultGateways as $gateway) {
            if (!isset($gateways[$gateway])) {
                $gateways[$gateway] = new PaymentSetting([
                    'gateway' => $gateway,
                    'display_name' => ucfirst($gateway),
                    'is_active' => false,
                    'is_test_mode' => true,
                ]);
            }
        }

        return view('admin.settings.payment', compact('gateways'));
    }

    /**
     * Update payment settings
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'gateway' => 'required|in:razorpay,stripe,paypal',
            'credentials' => 'required|array',
            'is_active' => 'boolean',
            'is_test_mode' => 'boolean',
        ]);

        // If activating this gateway, deactivate others
        if ($request->boolean('is_active')) {
            PaymentSetting::where('gateway', '!=', $validated['gateway'])
                ->update(['is_active' => false]);
        }

        PaymentSetting::updateOrCreate(
            ['gateway' => $validated['gateway']],
            [
                'display_name' => ucfirst($validated['gateway']),
                'credentials' => $validated['credentials'],
                'is_active' => $request->boolean('is_active'),
                'is_test_mode' => $request->boolean('is_test_mode'),
            ]
        );

        return back()->with('success', 'Payment settings updated successfully.');
    }
}
