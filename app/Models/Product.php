<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'category_id',
        'name',
        'slug',
        'sku',
        'barcode',
        'description',
        'price',
        'compare_price',
        'cost_price',
        'tax_rate',
        'stock_quantity',
        'low_stock_threshold',
        'image',
        'gallery',
        'status',
        'track_inventory',
        'is_featured',
        'sizes',
        'colors',
        'unit',
        'weight',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'gallery' => 'array',
        'track_inventory' => 'boolean',
        'is_featured' => 'boolean',
    ];

    /**
     * Accessor for sale_price - maps to compare_price
     * The compare_price field stores the sale/discounted price
     */
    public function getSalePriceAttribute()
    {
        return $this->compare_price;
    }

    /**
     * Mutator for sale_price - stores in compare_price
     */
    public function setSalePriceAttribute($value)
    {
        $this->attributes['compare_price'] = $value;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
            if (empty($product->sku)) {
                $product->sku = strtoupper(Str::random(8));
            }
        });
    }

    /**
     * Check if product is available
     */
    public function isAvailable(): bool
    {
        return $this->status === 'available' && (!$this->track_inventory || $this->stock_quantity > 0);
    }

    /**
     * Check if product is low on stock
     */
    public function isLowStock(): bool
    {
        return $this->track_inventory && $this->stock_quantity <= $this->low_stock_threshold;
    }

    /**
     * Reduce stock quantity
     */
    public function reduceStock(int $quantity): bool
    {
        if (!$this->track_inventory) {
            return true;
        }

        if ($this->stock_quantity < $quantity) {
            return false;
        }

        $this->decrement('stock_quantity', $quantity);
        return true;
    }

    /**
     * Restore stock quantity
     */
    public function restoreStock(int $quantity): void
    {
        if ($this->track_inventory) {
            $this->increment('stock_quantity', $quantity);
        }
    }

    /**
     * Get the store that owns the product
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get the category of the product
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Scope to get only available products
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    /**
     * Scope to get products in stock
     */
    public function scopeInStock($query)
    {
        return $query->where(function ($q) {
            $q->where('track_inventory', false)
              ->orWhere('stock_quantity', '>', 0);
        });
    }
}
