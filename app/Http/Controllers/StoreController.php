<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Store;
use App\Models\Cart;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    /**
     * Display the store page
     */
    public function show(string $slug, Request $request)
    {
        $store = Store::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $categories = $store->categories()
            ->where('is_active', true)
            ->withCount('products')
            ->orderBy('sort_order')
            ->get();

        $query = $store->products()
            ->where('is_active', true)
            ->with('category');
        
        // Filter by category
        if ($request->category) {
            $query->where('category_id', $request->category);
        }
        
        // Search
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('sku', 'like', '%' . $request->search . '%');
            });
        }
        
        // Sort
        switch ($request->sort) {
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'price_asc':
                $query->orderByRaw('COALESCE(sale_price, price) ASC');
                break;
            case 'price_desc':
                $query->orderByRaw('COALESCE(sale_price, price) DESC');
                break;
            default:
                $query->latest();
        }

        $products = $query->paginate(12);
        
        // Get cart count
        $cartCount = 0;
        if (auth()->check()) {
            $cart = Cart::where('user_id', auth()->id())->where('store_id', $store->id)->first();
            $cartCount = $cart ? $cart->items->sum('quantity') : 0;
        }

        return view('store.show', compact('store', 'categories', 'products', 'cartCount'));
    }

    /**
     * Show product details
     */
    public function product(string $slug, Product $product)
    {
        $store = Store::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        // Verify product belongs to store
        if ($product->store_id !== $store->id) {
            abort(404);
        }

        $relatedProducts = $store->products()
            ->where('id', '!=', $product->id)
            ->where('category_id', $product->category_id)
            ->where('is_active', true)
            ->take(4)
            ->get();

        return view('store.product', compact('store', 'product', 'relatedProducts'));
    }
}
