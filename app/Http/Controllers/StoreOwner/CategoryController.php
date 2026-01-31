<?php

namespace App\Http\Controllers\StoreOwner;

use App\Http\Controllers\Controller;
use App\Helpers\StorageHelper;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    /**
     * Display a listing of the categories
     */
    public function index()
    {
        $store = auth()->user()->getEffectiveStore();
        $categories = $store->categories()->orderBy('sort_order')->paginate(15);

        return view('store-owner.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category
     */
    public function create()
    {
        return view('store-owner.categories.create');
    }

    /**
     * Store a newly created category
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $store = auth()->user()->getEffectiveStore();

        if ($request->hasFile('image')) {
            $validated['image'] = StorageHelper::store($request->file('image'), 'categories');
        }

        $validated['store_id'] = $store->id;
        $validated['is_active'] = $request->boolean('is_active', true);

        Category::create($validated);

        return redirect()->route('store-owner.categories.index')
            ->with('success', 'Category created successfully.');
    }

    /**
     * Show the form for editing the specified category
     */
    public function edit(Category $category)
    {
        $this->authorizeCategory($category);

        return view('store-owner.categories.edit', compact('category'));
    }

    /**
     * Update the specified category
     */
    public function update(Request $request, Category $category)
    {
        $this->authorizeCategory($category);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            // Delete old image
            if ($category->image) {
                StorageHelper::delete($category->image);
            }
            $validated['image'] = StorageHelper::store($request->file('image'), 'categories');
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        $category->update($validated);

        return redirect()->route('store-owner.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified category
     */
    public function destroy(Category $category)
    {
        $this->authorizeCategory($category);

        if ($category->image) {
            StorageHelper::delete($category->image);
        }

        $category->delete();

        return redirect()->route('store-owner.categories.index')
            ->with('success', 'Category deleted successfully.');
    }

    /**
     * Authorize that the category belongs to the current store
     */
    private function authorizeCategory(Category $category): void
    {
        $store = auth()->user()->getEffectiveStore();
        if (!$store || $category->store_id !== $store->id) {
            abort(403);
        }
    }
}
