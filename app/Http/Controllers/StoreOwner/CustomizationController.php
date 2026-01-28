<?php

namespace App\Http\Controllers\StoreOwner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CustomizationController extends Controller
{
    /**
     * Display store customization settings
     */
    public function index()
    {
        $store = auth()->user()->getEffectiveStore();
        
        // Check if store has access to this feature
        if (!$store->hasFeature('store_customization')) {
            return redirect()->route('store-owner.settings.index')
                ->with('error', 'Store customization is not available in your current plan. Please upgrade to access this feature.');
        }

        return view('store-owner.settings.customization', compact('store'));
    }

    /**
     * Update store customization settings
     */
    public function update(Request $request)
    {
        $store = auth()->user()->getEffectiveStore();
        
        // Check if store has access to this feature
        if (!$store->hasFeature('store_customization')) {
            return redirect()->route('store-owner.settings.index')
                ->with('error', 'Store customization is not available in your current plan.');
        }

        $validated = $request->validate([
            'primary_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'secondary_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'accent_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'font_family' => 'nullable|string|max:100',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($store->logo && Storage::disk('public')->exists($store->logo)) {
                Storage::disk('public')->delete($store->logo);
            }
            $validated['logo'] = $request->file('logo')->store('stores', 'public');
        } else {
            unset($validated['logo']);
        }

        $store->update($validated);

        return back()->with('success', 'Store customization updated successfully.');
    }

    /**
     * Remove store logo
     */
    public function removeLogo()
    {
        $store = auth()->user()->getEffectiveStore();
        
        if ($store->logo && Storage::disk('public')->exists($store->logo)) {
            Storage::disk('public')->delete($store->logo);
        }
        
        $store->update(['logo' => null]);

        return back()->with('success', 'Store logo removed successfully.');
    }

    /**
     * Reset to default colors
     */
    public function resetColors()
    {
        $store = auth()->user()->getEffectiveStore();
        
        $store->update([
            'primary_color' => null,
            'secondary_color' => null,
            'accent_color' => null,
            'font_family' => null,
        ]);

        return back()->with('success', 'Colors reset to default successfully.');
    }
}
