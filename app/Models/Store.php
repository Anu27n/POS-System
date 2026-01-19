<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'description',
        'address',
        'phone',
        'email',
        'logo',
        'type',
        'qr_code',
        'status',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($store) {
            if (empty($store->slug)) {
                $store->slug = Str::slug($store->name);
            }
        });
    }

    /**
     * Check if store is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get the owner of the store
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the categories for the store
     */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    /**
     * Get the products for the store
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the orders for the store
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the public URL for the store
     */
    public function getPublicUrlAttribute(): string
    {
        return route('store.show', $this->slug);
    }
}
