<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
    ];

    /**
     * Get the cart that owns the item
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Get the product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the unit price (uses sale_price if available, otherwise regular price)
     */
    public function getPriceAttribute(): float
    {
        if (!$this->product) {
            return 0;
        }
        return (float) ($this->product->sale_price ?? $this->product->price ?? 0);
    }

    /**
     * Get the line total (subtotal)
     */
    public function getSubtotalAttribute(): float
    {
        return $this->price * $this->quantity;
    }

    /**
     * Alias for subtotal
     */
    public function getTotalAttribute(): float
    {
        return $this->subtotal;
    }
}
