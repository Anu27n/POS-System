<?php

namespace App\Http\Controllers\StoreOwner;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use App\Models\PaymentSetting;

class StoreCreateController extends Controller
{
    
    /**
     * Show the store creation form
     */
    public function create()
    {
        // Check if user already has a store
        if (auth()->user()->store) {
            return redirect()->route('store-owner.dashboard')
                ->with('info', 'You already have a store.');
        }

        // Check if any payment gateway is active
        $hasActiveGateway = PaymentSetting::where('is_active', true)->exists();
        if (!$hasActiveGateway) {
             // If no gateway is active, we can technically still show the form but maybe warn?
             // Or strictly, if "payment gateways are disabled, then don't allow store creation"
             return redirect()->route('store-owner.dashboard')
                ->with('error', 'Registration is currently closed because no payment gateways are active. Please contact support.');
        }

        return view('store-owner.stores.create');
    }

    /**
     * Store a newly created store
     */
    public function store(Request $request)
    {
        // Check if user already has a store
        if (auth()->user()->store) {
            return redirect()->route('store-owner.dashboard')
                ->with('error', 'You already have a store.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:grocery,clothing,department,restaurant,retail,other',
            'description' => 'nullable|string|max:1000',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'currency' => 'required|string|in:USD,EUR,GBP,INR',
        ]);

        // Check if any payment gateway is active
        $hasActiveGateway = PaymentSetting::where('is_active', true)->exists();
        
        if (!$hasActiveGateway) {
            return redirect()->back()
                ->with('error', 'Store creation is disabled as no payment gateways are active.');
        }

        // Save data to session and redirect to pricing
        session(['store_registration_data' => $validated]);

        return redirect()->route('pricing')
            ->with('info', 'Please select a plan to complete your store registration.');

        return redirect()->route('store-owner.dashboard')
            ->with('success', 'Store created successfully! You can now start adding products.');
    }
}
