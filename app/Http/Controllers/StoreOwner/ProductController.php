<?php

namespace App\Http\Controllers\StoreOwner;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the products
     */
    public function index(Request $request)
    {
        $store = auth()->user()->getEffectiveStore();
        $query = $store->products()->with('category');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('sku', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $products = $query->latest()->paginate(15);
        $categories = $store->categories()->orderBy('name')->get();

        return view('store-owner.products.index', compact('products', 'categories'));
    }

    /**
     * Show the form for creating a new product
     */
    public function create()
    {
        $store = auth()->user()->getEffectiveStore();
        $categories = $store->categories()->orderBy('name')->get();

        return view('store-owner.products.create', compact('categories'));
    }

    /**
     * Store a newly created product
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'sku' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'nullable',
            'is_featured' => 'nullable',
            'track_stock' => 'nullable',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'barcode' => 'nullable|string|max:100',
            'sizes' => 'nullable|string',
            'colors' => 'nullable|string',
            'unit' => 'nullable|string|max:50',
            'weight' => 'nullable|numeric|min:0',
        ]);

        $store = auth()->user()->getEffectiveStore();

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        // Map form fields to model fields
        $productData = [
            'store_id' => $store->id,
            'name' => $validated['name'],
            'category_id' => $validated['category_id'] ?? null,
            'sku' => $validated['sku'] ?? null,
            'barcode' => $validated['barcode'] ?? null,
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'compare_price' => $validated['sale_price'] ?? null,
            'cost_price' => $validated['cost_price'] ?? null,
            'tax_rate' => $validated['tax_rate'] ?? 0,
            'stock_quantity' => $validated['stock_quantity'] ?? 0,
            'low_stock_threshold' => $validated['low_stock_threshold'] ?? 5,
            'image' => $validated['image'] ?? null,
            'status' => ($request->input('is_active') == '1') ? 'available' : 'unavailable',
            'track_inventory' => ($request->input('track_stock') == '1'),
            'is_featured' => ($request->input('is_featured') == '1'),
            'sizes' => $validated['sizes'] ?? null,
            'colors' => $validated['colors'] ?? null,
            'unit' => $validated['unit'] ?? null,
            'weight' => $validated['weight'] ?? null,
        ];

        Product::create($productData);

        return redirect()->route('store-owner.products.index')
            ->with('success', 'Product created successfully.');
    }

    /**
     * Display the specified product
     */
    public function show(Product $product)
    {
        $this->authorizeProduct($product);

        return view('store-owner.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified product
     */
    public function edit(Product $product)
    {
        $this->authorizeProduct($product);

        $store = auth()->user()->getEffectiveStore();
        $categories = $store->categories()->orderBy('name')->get();

        return view('store-owner.products.edit', compact('product', 'categories'));
    }

    /**
     * Update the specified product
     */
    public function update(Request $request, Product $product)
    {
        $this->authorizeProduct($product);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'sku' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'nullable',
            'is_featured' => 'nullable',
            'track_stock' => 'nullable',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'barcode' => 'nullable|string|max:100',
            'sizes' => 'nullable|string',
            'colors' => 'nullable|string',
            'unit' => 'nullable|string|max:50',
            'weight' => 'nullable|numeric|min:0',
        ]);

        if ($request->hasFile('image')) {
            // Delete old image
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        // Map form fields to model fields
        $productData = [
            'name' => $validated['name'],
            'category_id' => $validated['category_id'] ?? null,
            'sku' => $validated['sku'] ?? null,
            'barcode' => $validated['barcode'] ?? null,
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'compare_price' => $validated['sale_price'] ?? null,
            'cost_price' => $validated['cost_price'] ?? null,
            'tax_rate' => $validated['tax_rate'] ?? 0,
            'stock_quantity' => $validated['stock_quantity'] ?? 0,
            'low_stock_threshold' => $validated['low_stock_threshold'] ?? 5,
            'status' => ($request->input('is_active') == '1') ? 'available' : 'unavailable',
            'track_inventory' => ($request->input('track_stock') == '1'),
            'is_featured' => ($request->input('is_featured') == '1'),
            'sizes' => $validated['sizes'] ?? null,
            'colors' => $validated['colors'] ?? null,
            'unit' => $validated['unit'] ?? null,
            'weight' => $validated['weight'] ?? null,
        ];

        if (isset($validated['image'])) {
            $productData['image'] = $validated['image'];
        }

        $product->update($productData);

        return redirect()->route('store-owner.products.index')
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified product
     */
    public function destroy(Product $product)
    {
        $this->authorizeProduct($product);

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('store-owner.products.index')
            ->with('success', 'Product deleted successfully.');
    }

    /**
     * Update stock quantity
     */
    public function updateStock(Request $request, Product $product)
    {
        $this->authorizeProduct($product);

        $validated = $request->validate([
            'stock_quantity' => 'required|integer|min:0',
        ]);

        $product->update($validated);

        return back()->with('success', 'Stock updated successfully.');
    }

    /**
     * Authorize that the product belongs to the current store
     */
    private function authorizeProduct(Product $product): void
    {
        $store = auth()->user()->getEffectiveStore();
        if (!$store || $product->store_id !== $store->id) {
            abort(403);
        }
    }
}
