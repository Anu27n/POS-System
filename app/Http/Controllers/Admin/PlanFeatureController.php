<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlanFeature;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PlanFeatureController extends Controller
{
    /**
     * Display a listing of features
     */
    public function index()
    {
        $features = PlanFeature::orderBy('category')->orderBy('sort_order')->get();
        $groupedFeatures = $features->groupBy('category');
        $categories = PlanFeature::CATEGORIES;

        return view('admin.plans.features.index', compact('features', 'groupedFeatures', 'categories'));
    }

    /**
     * Show the form for creating a new feature
     */
    public function create()
    {
        $categories = PlanFeature::CATEGORIES;
        return view('admin.plans.features.create', compact('categories'));
    }

    /**
     * Store a newly created feature
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'category' => 'required|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['slug'] = Str::slug($validated['name'], '_');
        $validated['is_active'] = $request->boolean('is_active', true);

        PlanFeature::create($validated);

        return redirect()->route('admin.plan-features.index')
            ->with('success', 'Feature created successfully.');
    }

    /**
     * Show the form for editing the specified feature
     */
    public function edit(PlanFeature $planFeature)
    {
        $categories = PlanFeature::CATEGORIES;
        return view('admin.plans.features.edit', compact('planFeature', 'categories'));
    }

    /**
     * Update the specified feature
     */
    public function update(Request $request, PlanFeature $planFeature)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'category' => 'required|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $planFeature->update($validated);

        return redirect()->route('admin.plan-features.index')
            ->with('success', 'Feature updated successfully.');
    }

    /**
     * Remove the specified feature
     */
    public function destroy(PlanFeature $planFeature)
    {
        $planFeature->delete();

        return redirect()->route('admin.plan-features.index')
            ->with('success', 'Feature deleted successfully.');
    }

    /**
     * Seed default features
     */
    public function seedDefaults()
    {
        foreach (PlanFeature::DEFAULT_FEATURES as $index => $feature) {
            PlanFeature::firstOrCreate(
                ['slug' => $feature['slug']],
                array_merge($feature, ['sort_order' => $index])
            );
        }

        return redirect()->route('admin.plan-features.index')
            ->with('success', 'Default features have been seeded.');
    }
}
