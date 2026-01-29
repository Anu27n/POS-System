<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\User;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class StoreController extends Controller
{
    /**
     * Display a listing of the stores
     */
    public function index(Request $request)
    {
        $query = Store::with('owner');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $stores = $query->latest()->paginate(15);

        return view('admin.stores.index', compact('stores'));
    }

    /**
     * Show the form for creating a new store
     */
    public function create()
    {
        $storeOwners = User::where('role', 'store_owner')
            ->whereDoesntHave('store')
            ->get();

        return view('admin.stores.create', compact('storeOwners'));
    }

    /**
     * Store a newly created store
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'type' => 'required|in:grocery,clothing,department,general',
            'status' => 'required|in:active,inactive',
            'user_id' => 'nullable|exists:users,id',
            // New owner fields
            'create_new_owner' => 'nullable|boolean',
            'owner_name' => 'required_if:create_new_owner,1|string|max:255',
            'owner_email' => 'required_if:create_new_owner,1|email|unique:users,email',
            'owner_password' => ['required_if:create_new_owner,1', Password::defaults()],
        ]);

        // Create new store owner if requested
        if ($request->boolean('create_new_owner')) {
            $owner = User::create([
                'name' => $validated['owner_name'],
                'email' => $validated['owner_email'],
                'password' => Hash::make($validated['owner_password']),
                'role' => 'store_owner',
            ]);
            $validated['user_id'] = $owner->id;
        }

        $store = Store::create($validated);

        return redirect()->route('admin.stores.index')
            ->with('success', 'Store created successfully.');
    }

    /**
     * Display the specified store
     */
    public function show(Store $store)
    {
        $store->load(['owner', 'products', 'orders' => function ($query) {
            $query->latest()->take(10);
        }]);

        return view('admin.stores.show', compact('store'));
    }

    /**
     * Show the form for editing the specified store
     */
    public function edit(Store $store)
    {
        $storeOwners = User::where('role', 'store_owner')
            ->where(function ($query) use ($store) {
                $query->whereDoesntHave('store')
                    ->orWhere('id', $store->user_id);
            })
            ->get();

        $plans = Plan::where('is_active', true)->orderBy('price')->get();
        $currentSubscription = $store->subscriptions()->where('status', 'active')->first();

        return view('admin.stores.edit', compact('store', 'storeOwners', 'plans', 'currentSubscription'));
    }

    /**
     * Update the specified store
     */
    public function update(Request $request, Store $store)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'type' => 'required|in:grocery,clothing,department,general',
            'status' => 'required|in:active,inactive',
            'user_id' => 'nullable|exists:users,id',
            'plan_id' => 'nullable|exists:plans,id',
        ]);

        $store->update($validated);

        // Handle subscription plan change
        if ($request->filled('plan_id')) {
            $plan = Plan::find($request->plan_id);
            
            // Cancel current active subscription if exists
            $store->subscriptions()->where('status', 'active')->update(['status' => 'cancelled']);
            
            // Create new subscription
            Subscription::create([
                'store_id' => $store->id,
                'plan_id' => $plan->id,
                'status' => 'active',
                'amount_paid' => $plan->price,
                'start_date' => now(),
                'end_date' => $plan->billing_cycle === 'monthly' ? now()->addMonth() : now()->addYear(),
                'payment_method' => 'admin_assigned',
            ]);
        }

        return redirect()->route('admin.stores.index')
            ->with('success', 'Store updated successfully.');
    }

    /**
     * Remove the specified store
     */
    public function destroy(Store $store)
    {
        $store->delete();

        return redirect()->route('admin.stores.index')
            ->with('success', 'Store deleted successfully.');
    }

    /**
     * Toggle store status
     */
    public function toggleStatus(Store $store)
    {
        $store->update([
            'status' => $store->status === 'active' ? 'inactive' : 'active',
        ]);

        return back()->with('success', 'Store status updated successfully.');
    }
}
