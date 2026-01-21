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
        'tax_rate',
        'currency',
        'qr_code',
        'status',
        'enable_online_payment',
        'enable_counter_payment',
        'razorpay_key_id',
        'razorpay_key_secret',
        'razorpay_enabled',
        'stripe_publishable_key',
        'stripe_secret_key',
        'stripe_enabled',
        'is_test_mode',
    ];

    protected $casts = [
        'tax_rate' => 'decimal:2',
        'enable_online_payment' => 'boolean',
        'enable_counter_payment' => 'boolean',
        'razorpay_enabled' => 'boolean',
        'stripe_enabled' => 'boolean',
        'is_test_mode' => 'boolean',
    ];

    protected $hidden = [
        'razorpay_key_secret',
        'stripe_secret_key',
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
     * Get the staff for the store
     */
    public function staff(): HasMany
    {
        return $this->hasMany(Staff::class);
    }

    /**
     * Get the customers for the store
     */
    public function customers(): HasMany
    {
        return $this->hasMany(StoreCustomer::class);
    }

    /**
     * Get the public URL for the store
     */
    public function getPublicUrlAttribute(): string
    {
        return route('store.show', $this->slug);
    }

    /**
     * Check if Razorpay is enabled
     */
    public function isRazorpayEnabled(): bool
    {
        return $this->enable_online_payment && 
               $this->razorpay_enabled && 
               !empty($this->razorpay_key_id) && 
               !empty($this->razorpay_key_secret);
    }

    /**
     * Check if Stripe is enabled
     */
    public function isStripeEnabled(): bool
    {
        return $this->enable_online_payment && 
               $this->stripe_enabled && 
               !empty($this->stripe_publishable_key) && 
               !empty($this->stripe_secret_key);
    }

    /**
     * Check if any online payment is available
     */
    public function hasOnlinePayment(): bool
    {
        return $this->isRazorpayEnabled() || $this->isStripeEnabled();
    }

    /**
     * Get available payment methods
     */
    public function getAvailablePaymentMethods(): array
    {
        $methods = [];
        
        if ($this->enable_counter_payment) {
            $methods['counter'] = 'Pay at Counter';
        }
        
        if ($this->isRazorpayEnabled()) {
            $methods['razorpay'] = 'Pay with Razorpay';
        }
        
        if ($this->isStripeEnabled()) {
            $methods['stripe'] = 'Pay with Stripe';
        }
        
        return $methods;
    }
}
