<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Display the cart
     */
    public function index(Request $request)
    {
        $cart = $this->getCart();

        if (!$cart || $cart->items->isEmpty()) {
            return view('cart.index', ['cart' => null, 'subtotal' => 0, 'tax' => 0, 'taxBreakdown' => [], 'total' => 0]);
        }

        $cart->load(['items.product', 'store']);
        $store = $cart->store;
        $subtotal = $cart->items->sum('subtotal');
        
        // Calculate taxes using new tax system (same as checkout)
        $taxSettings = $store->taxSettings;
        $tax = 0;
        $taxBreakdown = [];
        
        if ($taxSettings && $taxSettings->taxes_enabled) {
            foreach ($store->enabledTaxes as $storeTax) {
                $taxAmount = $storeTax->calculateTax($subtotal);
                $tax += $taxAmount;
                $taxBreakdown[] = [
                    'name' => $storeTax->name,
                    'percentage' => $storeTax->percentage,
                    'amount' => $taxAmount,
                ];
            }
        }
        
        $total = $subtotal + $tax;

        return view('cart.index', compact('cart', 'subtotal', 'tax', 'taxBreakdown', 'total'));
    }

    /**
     * Add product to cart
     */
    public function add(Request $request)
    {
        $validated = $request->validate([
            'store_id' => 'required|exists:stores,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'nullable|integer|min:1',
        ]);

        $store = Store::findOrFail($validated['store_id']);
        $product = Product::where('id', $validated['product_id'])
            ->where('store_id', $store->id)
            ->where('status', 'available')
            ->firstOrFail();

        $quantity = $validated['quantity'] ?? 1;

        // Check stock
        if ($product->track_inventory && $product->stock_quantity < $quantity) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Not enough stock available.']);
            }
            return back()->with('error', 'Not enough stock available.');
        }

        $cart = $this->getOrCreateCart($store);

        // Check if item already exists - use updateOrCreate to avoid unique constraint violation
        $existingItem = $cart->items()->where('product_id', $product->id)->first();

        if ($existingItem) {
            $existingItem->quantity += $quantity;
            $existingItem->save();
        } else {
            $cart->items()->create([
                'product_id' => $product->id,
                'quantity' => $quantity,
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Product added to cart.',
                'cartCount' => $cart->fresh()->items->sum('quantity'),
            ]);
        }

        return back()->with('success', 'Product added to cart.');
    }

    /**
     * Update cart item quantity
     */
    public function update(Request $request, CartItem $cartItem)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:0',
        ]);

        if ($validated['quantity'] == 0) {
            $cartItem->delete();
        } else {
            // Check stock
            if ($cartItem->product && $cartItem->product->track_inventory && $validated['quantity'] > $cartItem->product->stock_quantity) {
                return back()->with('error', 'Not enough stock available.');
            }

            $cartItem->quantity = $validated['quantity'];
            $cartItem->save();
        }

        return back()->with('success', 'Cart updated.');
    }

    /**
     * Remove item from cart
     */
    public function remove(Request $request, CartItem $cartItem)
    {
        $cartItem->delete();
        return back()->with('success', 'Product removed from cart.');
    }

    /**
     * Clear the cart
     */
    public function clear()
    {
        $cart = $this->getCart();
        if ($cart) {
            $cart->items()->delete();
        }
        return back()->with('success', 'Cart cleared.');
    }

    /**
     * Get cart for the current user/session
     */
    private function getCart(): ?Cart
    {
        if (auth()->check()) {
            return Cart::where('user_id', auth()->id())->with('items.product', 'store')->first();
        }

        return Cart::where('session_id', session()->getId())->with('items.product', 'store')->first();
    }

    /**
     * Get or create cart for the current user/session
     */
    private function getOrCreateCart(Store $store): Cart
    {
        // If user switches stores, clear old cart
        $existingCart = $this->getCart();
        if ($existingCart && $existingCart->store_id !== $store->id) {
            $existingCart->items()->delete();
            $existingCart->delete();
        }

        if (auth()->check()) {
            return Cart::firstOrCreate([
                'user_id' => auth()->id(),
                'store_id' => $store->id,
            ]);
        }

        return Cart::firstOrCreate([
            'session_id' => session()->getId(),
            'store_id' => $store->id,
        ]);
    }
}
