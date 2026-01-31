<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\Store;
use App\Models\Subscription;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PricingController extends Controller
{
    /**
     * Helper to create store from session data
     */
    private function createStoreFromSession()
    {
        $data = session('store_registration_data');
        if (!$data) {
            return null;
        }

        // Generate unique slug
        $slug = Str::slug($data['name']);
        $originalSlug = $slug;
        $counter = 1;
        while (Store::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }

        $store = Store::create([
            'user_id' => auth()->id(),
            'name' => $data['name'],
            'slug' => $slug,
            'type' => $data['type'],
            'description' => $data['description'] ?? null,
            'address' => $data['address'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'currency' => $data['currency'],
            'status' => 'active',
        ]);

        session()->forget('store_registration_data');
        return $store;
    }

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

        // Check if user has a store OR has registration data in session
        $store = $user->store;
        
        // If no store and no session data, redirect to create store
        if (!$store && !session()->has('store_registration_data')) {
            return redirect()->route('store-owner.stores.create')
                ->with('error', 'Please create your store details first.');
        }

        // Check for existing active subscription (only if store exists)
        $existingSubscription = $store ? $store->activeSubscription : null;

        // If using session data, we pass a dummy/partial store object or null to view if needed
        // But the view likely expects a store object. Let's see if we can get by with just null if the view handles it.
        // Or we can construct a temporary object.
        if (!$store && session()->has('store_registration_data')) {
            $store = new Store(session('store_registration_data'));
        }

        return view('checkout.plan', compact('plan', 'store', 'existingSubscription'));
    }

    /**
     * Process plan subscription
     */
    public function subscribe(Request $request, Plan $plan)
    {
        $user = auth()->user();
        $store = $user->store;

        // If no store, try to create from session (only for free plans logic here, 
        // for paid plans we might wait until payment callback OR create here if we want to associate payment source immediately)
        // Actually, for consistency, let's create the store context if needed.
        
        if (!$store && !session()->has('store_registration_data')) {
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
            // If store doesn't exist, create it now
            if (!$store->exists) {
                $createdStore = $this->createStoreFromSession();
                if ($createdStore) {
                    $store = $createdStore;
                } else {
                    throw new \Exception('Failed to create store from session.');
                }
            }

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
            if (session()->has('store_registration_data')) {
                $store = new Store(session('store_registration_data'));
            } else {
                return redirect()->route('pricing')->with('error', 'Store not found.');
            }
        }

        return view('checkout.payment', compact('plan', 'store', 'method'));
    }

    /**
     * Handle payment callback (Razorpay)
     */
    public function razorpayCallback(Request $request, Plan $plan)
    {
        $store = auth()->user()->store;
        $isNewStore = false;

        // Only create store if it doesn't exist
        if (!$store && session()->has('store_registration_data')) {
             // We will create it inside the transaction
             $isNewStore = true;
        } elseif (!$store) {
             return redirect()->route('pricing')->with('error', 'Store not found session expired.');
        }

        $validated = $request->validate([
            'razorpay_payment_id' => 'required|string',
            'razorpay_order_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        // Verify payment signature here (implementation depends on Razorpay SDK)
        // For now, we'll create the subscription

        DB::beginTransaction();
        try {
            if ($isNewStore) {
                $store = $this->createStoreFromSession();
                if (!$store) {
                    throw new \Exception('Failed to create store from session.');
                }
            }

            $store->subscriptions()->where('status', 'active')->update(['status' => 'cancelled']);

            $amount = $plan->price;
            if ($plan->tax_enabled && $plan->tax_percentage > 0) {
                $amount += ($plan->price * $plan->tax_percentage) / 100;
            }

            $subscription = Subscription::create([
                'store_id' => $store->id,
                'plan_id' => $plan->id,
                'status' => 'active',
                'starts_at' => now(),
                'ends_at' => $this->calculateEndDate($plan->billing_cycle),
                'payment_method' => 'razorpay',
                'transaction_id' => $validated['razorpay_payment_id'],
                'amount_paid' => $amount,
            ]);

            // Create payment record
            $subscription->payments()->create([
                'store_id' => $store->id,
                'amount' => $amount,
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

    /**
     * Handle payment callback (Stripe)
     */
    public function stripeCallback(Request $request, Plan $plan)
    {
        $store = auth()->user()->store;
        $isNewStore = false;

        if (!$store && session()->has('store_registration_data')) {
            $isNewStore = true;
        } elseif (!$store) {
            return redirect()->route('pricing')->with('error', 'Store not found.');
        }

        $validated = $request->validate([
            'stripe_token' => 'required|string',
        ]);

        // In production, you would:
        // 1. Use Stripe SDK to create a charge with the token
        // 2. Verify the charge was successful
        // 3. Then create the subscription
        
        // For now, we'll simulate a successful payment
        // In production, add: \Stripe\Stripe::setApiKey($stripeSecretKey);
        // $charge = \Stripe\Charge::create([...]);

        DB::beginTransaction();
        try {
             if ($isNewStore) {
                $store = $this->createStoreFromSession();
                if (!$store) {
                    throw new \Exception('Failed to create store from session.');
                }
            }

            $store->subscriptions()->where('status', 'active')->update(['status' => 'cancelled']);

            $amount = $plan->price;
            if ($plan->tax_enabled && $plan->tax_percentage > 0) {
                $amount += ($plan->price * $plan->tax_percentage) / 100;
            }

            $subscription = Subscription::create([
                'store_id' => $store->id,
                'plan_id' => $plan->id,
                'status' => 'active',
                'starts_at' => now(),
                'ends_at' => $this->calculateEndDate($plan->billing_cycle),
                'payment_method' => 'stripe',
                'transaction_id' => 'stripe_' . $validated['stripe_token'],
                'amount_paid' => $amount,
            ]);

            // Create payment record
            $subscription->payments()->create([
                'store_id' => $store->id,
                'amount' => $amount,
                'currency' => 'INR',
                'payment_method' => 'stripe',
                'transaction_id' => 'stripe_' . $validated['stripe_token'],
                'status' => 'completed',
                'paid_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('store-owner.dashboard')
                ->with('success', 'Payment successful! Welcome to ' . $plan->name . '!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('pricing')
                ->with('error', 'Payment processing failed: ' . $e->getMessage());
        }
    }
}
