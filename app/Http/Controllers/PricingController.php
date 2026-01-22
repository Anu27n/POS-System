<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\Store;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PricingController extends Controller
{
    /**
     * Display pricing plans
     */
    public function index()
    {
        $plans = Plan::active()
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get();

        $features = PlanFeature::active()
            ->orderBy('category')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('category');

        $categories = PlanFeature::CATEGORIES;

        return view('pricing', compact('plans', 'features', 'categories'));
    }

    /**
     * Show checkout page for a plan
     */
    public function checkout(Plan $plan)
    {
        if (!$plan->is_active) {
            return redirect()->route('pricing')->with('error', 'This plan is no longer available.');
        }

        $user = auth()->user();
        
        // Check if user has a store
        if (!$user || !$user->isStoreOwner()) {
            return redirect()->route('pricing')
                ->with('error', 'You need to register as a store owner to purchase a plan.');
        }

        $store = $user->store;
        if (!$store) {
            return redirect()->route('store-owner.dashboard')
                ->with('error', 'Please create your store first before purchasing a plan.');
        }

        // Check for existing active subscription
        $existingSubscription = $store->activeSubscription;

        return view('checkout.plan', compact('plan', 'store', 'existingSubscription'));
    }

    /**
     * Process plan subscription
     */
    public function subscribe(Request $request, Plan $plan)
    {
        $user = auth()->user();
        $store = $user->store;

        if (!$store) {
            return redirect()->route('pricing')
                ->with('error', 'You need a store to subscribe to a plan.');
        }

        // For free plans or trial
        if ($plan->price == 0 || $plan->trial_days > 0) {
            return $this->createFreeOrTrialSubscription($store, $plan);
        }

        // Validate payment method
        $validated = $request->validate([
            'payment_method' => 'required|in:razorpay,stripe',
        ]);

        // Redirect to payment processing
        return redirect()->route('pricing.payment', [
            'plan' => $plan,
            'method' => $validated['payment_method']
        ]);
    }

    /**
     * Create free or trial subscription
     */
    private function createFreeOrTrialSubscription(Store $store, Plan $plan)
    {
        DB::beginTransaction();
        try {
            // Cancel existing subscription if any
            $store->subscriptions()->where('status', 'active')->update(['status' => 'cancelled']);

            $subscription = Subscription::create([
                'store_id' => $store->id,
                'plan_id' => $plan->id,
                'status' => $plan->trial_days > 0 ? 'trial' : 'active',
                'trial_ends_at' => $plan->trial_days > 0 ? now()->addDays($plan->trial_days) : null,
                'starts_at' => now(),
                'ends_at' => $plan->trial_days > 0 
                    ? now()->addDays($plan->trial_days) 
                    : $this->calculateEndDate($plan->billing_cycle),
                'amount_paid' => 0,
            ]);

            DB::commit();

            return redirect()->route('store-owner.dashboard')
                ->with('success', 'Successfully subscribed to ' . $plan->name . '!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('pricing')
                ->with('error', 'Failed to create subscription. Please try again.');
        }
    }

    /**
     * Calculate subscription end date
     */
    private function calculateEndDate(string $billingCycle): \Carbon\Carbon
    {
        return match ($billingCycle) {
            'monthly' => now()->addMonth(),
            'quarterly' => now()->addMonths(3),
            'yearly' => now()->addYear(),
            default => now()->addMonth(),
        };
    }

    /**
     * Payment processing page
     */
    public function payment(Request $request, Plan $plan)
    {
        $method = $request->input('method', 'razorpay');
        $store = auth()->user()->store;

        if (!$store) {
            return redirect()->route('pricing')->with('error', 'Store not found.');
        }

        return view('checkout.payment', compact('plan', 'store', 'method'));
    }

    /**
     * Handle payment callback (Razorpay)
     */
    public function razorpayCallback(Request $request, Plan $plan)
    {
        $store = auth()->user()->store;

        $validated = $request->validate([
            'razorpay_payment_id' => 'required|string',
            'razorpay_order_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        // Verify payment signature here (implementation depends on Razorpay SDK)
        // For now, we'll create the subscription

        DB::beginTransaction();
        try {
            $store->subscriptions()->where('status', 'active')->update(['status' => 'cancelled']);

            $subscription = Subscription::create([
                'store_id' => $store->id,
                'plan_id' => $plan->id,
                'status' => 'active',
                'starts_at' => now(),
                'ends_at' => $this->calculateEndDate($plan->billing_cycle),
                'payment_method' => 'razorpay',
                'transaction_id' => $validated['razorpay_payment_id'],
                'amount_paid' => $plan->price,
            ]);

            // Create payment record
            $subscription->payments()->create([
                'store_id' => $store->id,
                'amount' => $plan->price,
                'currency' => 'INR',
                'payment_method' => 'razorpay',
                'transaction_id' => $validated['razorpay_payment_id'],
                'status' => 'completed',
                'paid_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('store-owner.dashboard')
                ->with('success', 'Payment successful! Welcome to ' . $plan->name . '!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('pricing')
                ->with('error', 'Payment processing failed. Please contact support.');
        }
    }
}
