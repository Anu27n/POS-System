<?php

namespace App\Http\Controllers\StoreOwner;

use App\Http\Controllers\Controller;
use App\Models\StoreCustomer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers
     */
    public function index(Request $request)
    {
        $store = auth()->user()->getEffectiveStore();

        $query = $store->customers()->orderBy('name');

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter
        if ($request->input('filter') === 'manual') {
            $query->where('is_manually_added', true);
        } elseif ($request->input('filter') === 'checkout') {
            $query->where('is_manually_added', false);
        }

        $customers = $query->paginate(20);

        return view('store-owner.customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new customer
     */
    public function create()
    {
        return view('store-owner.customers.create');
    }

    /**
     * Store a newly created customer
     */
    public function store(Request $request)
    {
        $store = auth()->user()->getEffectiveStore();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Check if customer with same email/phone exists
        if (!empty($validated['email'])) {
            $existing = $store->customers()->where('email', $validated['email'])->first();
            if ($existing) {
                return back()->withInput()
                    ->with('error', 'A customer with this email already exists.');
            }
        }

        if (!empty($validated['phone'])) {
            $existing = $store->customers()->where('phone', $validated['phone'])->first();
            if ($existing) {
                return back()->withInput()
                    ->with('error', 'A customer with this phone number already exists.');
            }
        }

        StoreCustomer::create([
            'store_id' => $store->id,
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'is_manually_added' => true,
        ]);

        return redirect()->route('store-owner.customers.index')
            ->with('success', 'Customer added successfully.');
    }

    /**
     * Show customer details
     */
    public function show(StoreCustomer $customer)
    {
        $store = auth()->user()->getEffectiveStore();

        if ($customer->store_id !== $store->id) {
            abort(403);
        }

        // Get customer's orders if they have a user account
        $orders = [];
        if ($customer->user_id) {
            $orders = $store->orders()
                ->where('user_id', $customer->user_id)
                ->latest()
                ->take(10)
                ->get();
        }

        return view('store-owner.customers.show', compact('customer', 'orders'));
    }

    /**
     * Show the form for editing a customer
     */
    public function edit(StoreCustomer $customer)
    {
        $store = auth()->user()->getEffectiveStore();

        if ($customer->store_id !== $store->id) {
            abort(403);
        }

        return view('store-owner.customers.edit', compact('customer'));
    }

    /**
     * Update the specified customer
     */
    public function update(Request $request, StoreCustomer $customer)
    {
        $store = auth()->user()->getEffectiveStore();

        if ($customer->store_id !== $store->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Check for duplicates excluding current customer
        if (!empty($validated['email'])) {
            $existing = $store->customers()
                ->where('email', $validated['email'])
                ->where('id', '!=', $customer->id)
                ->first();
            if ($existing) {
                return back()->withInput()
                    ->with('error', 'Another customer with this email already exists.');
            }
        }

        if (!empty($validated['phone'])) {
            $existing = $store->customers()
                ->where('phone', $validated['phone'])
                ->where('id', '!=', $customer->id)
                ->first();
            if ($existing) {
                return back()->withInput()
                    ->with('error', 'Another customer with this phone number already exists.');
            }
        }

        $customer->update($validated);

        return redirect()->route('store-owner.customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    /**
     * Remove the specified customer
     */
    public function destroy(StoreCustomer $customer)
    {
        $store = auth()->user()->getEffectiveStore();

        if ($customer->store_id !== $store->id) {
            abort(403);
        }

        $customer->delete();

        return redirect()->route('store-owner.customers.index')
            ->with('success', 'Customer removed successfully.');
    }
}
