<?php

namespace App\Http\Controllers\StoreOwner;

use App\Http\Controllers\Controller;
use App\Models\StoreTax;
use App\Models\StoreTaxSetting;
use Illuminate\Http\Request;

class TaxSettingController extends Controller
{
    /**
     * Display tax settings page
     */
    public function index()
    {
        $store = auth()->user()->getEffectiveStore();
        $taxSettings = $store->taxSettings ?? StoreTaxSetting::create([
            'store_id' => $store->id,
            'taxes_enabled' => false,
            'tax_type' => 'order_level',
        ]);
        $taxes = $store->taxes()->orderBy('sort_order')->get();

        return view('store-owner.settings.taxes', compact('store', 'taxSettings', 'taxes'));
    }

    /**
     * Update tax settings
     */
    public function updateSettings(Request $request)
    {
        $store = auth()->user()->getEffectiveStore();
        
        $validated = $request->validate([
            'taxes_enabled' => 'boolean',
            'tax_type' => 'required|in:item_level,order_level',
            'tax_number' => 'nullable|string|max:50',
            'show_tax_on_receipt' => 'boolean',
            'tax_inclusive_pricing' => 'boolean',
        ]);

        $validated['taxes_enabled'] = $request->boolean('taxes_enabled');
        $validated['show_tax_on_receipt'] = $request->boolean('show_tax_on_receipt');
        $validated['tax_inclusive_pricing'] = $request->boolean('tax_inclusive_pricing');

        StoreTaxSetting::updateOrCreate(
            ['store_id' => $store->id],
            $validated
        );

        return redirect()->route('store-owner.tax-settings.index')
            ->with('success', 'Tax settings updated successfully.');
    }

    /**
     * Store a new tax
     */
    public function storeTax(Request $request)
    {
        $store = auth()->user()->getEffectiveStore();

        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'percentage' => 'required|numeric|min:0|max:100',
            'is_enabled' => 'boolean',
        ]);

        $validated['store_id'] = $store->id;
        $validated['is_enabled'] = $request->boolean('is_enabled', true);
        $validated['sort_order'] = $store->taxes()->count();

        StoreTax::create($validated);

        return redirect()->route('store-owner.tax-settings.index')
            ->with('success', 'Tax added successfully.');
    }

    /**
     * Update a tax
     */
    public function updateTax(Request $request, StoreTax $tax)
    {
        $store = auth()->user()->getEffectiveStore();

        if ($tax->store_id !== $store->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'percentage' => 'required|numeric|min:0|max:100',
            'is_enabled' => 'boolean',
        ]);

        $validated['is_enabled'] = $request->boolean('is_enabled');

        $tax->update($validated);

        return redirect()->route('store-owner.tax-settings.index')
            ->with('success', 'Tax updated successfully.');
    }

    /**
     * Delete a tax
     */
    public function destroyTax(StoreTax $tax)
    {
        $store = auth()->user()->getEffectiveStore();

        if ($tax->store_id !== $store->id) {
            abort(403);
        }

        $tax->delete();

        return redirect()->route('store-owner.tax-settings.index')
            ->with('success', 'Tax deleted successfully.');
    }

    /**
     * Toggle tax status
     */
    public function toggleTax(StoreTax $tax)
    {
        $store = auth()->user()->getEffectiveStore();

        if ($tax->store_id !== $store->id) {
            abort(403);
        }

        $tax->update(['is_enabled' => !$tax->is_enabled]);

        return redirect()->route('store-owner.tax-settings.index')
            ->with('success', 'Tax status updated.');
    }
}
