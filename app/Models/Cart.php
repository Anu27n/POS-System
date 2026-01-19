<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'store_id',
    ];

    /**
     * Get the user that owns the cart
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the store for the cart
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get the cart items
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Calculate the subtotal of the cart
     */
    public function getSubtotalAttribute(): float
    {
        return $this->items->sum(function ($item) {
            return $item->product->price * $item->quantity;
        });
    }

    /**
     * Get the total items count
     */
    public function getTotalItemsAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    /**
     * Add a product to the cart
     */
    public function addProduct(Product $product, int $quantity = 1): CartItem
    {
        $item = $this->items()->where('product_id', $product->id)->first();

        if ($item) {
            $item->increment('quantity', $quantity);
            return $item->fresh();
        }

        return $this->items()->create([
            'product_id' => $product->id,
            'quantity' => $quantity,
        ]);
    }

    /**
     * Update product quantity in cart
     */
    public function updateProductQuantity(Product $product, int $quantity): ?CartItem
    {
        $item = $this->items()->where('product_id', $product->id)->first();

        if ($item) {
            if ($quantity <= 0) {
                $item->delete();
                return null;
            }
            $item->update(['quantity' => $quantity]);
            return $item->fresh();
        }

        return null;
    }

    /**
     * Remove a product from the cart
     */
    public function removeProduct(Product $product): bool
    {
        return $this->items()->where('product_id', $product->id)->delete() > 0;
    }

    /**
     * Clear all items from the cart
     */
    public function clear(): void
    {
        $this->items()->delete();
    }

    /**
     * Check if cart is empty
     */
    public function isEmpty(): bool
    {
        return $this->items->isEmpty();
    }
}
