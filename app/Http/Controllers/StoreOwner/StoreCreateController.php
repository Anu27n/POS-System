<?php

namespace App\Http\Controllers\StoreOwner;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

        // Generate unique slug
        $slug = Str::slug($validated['name']);
        $originalSlug = $slug;
        $counter = 1;
        while (Store::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }

        $store = Store::create([
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'slug' => $slug,
            'type' => $validated['type'],
            'description' => $validated['description'] ?? null,
            'address' => $validated['address'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'currency' => $validated['currency'],
            'status' => 'active',
        ]);

        return redirect()->route('store-owner.dashboard')
            ->with('success', 'Store created successfully! You can now start adding products.');
    }
}
