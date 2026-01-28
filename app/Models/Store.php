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
        'primary_color',
        'secondary_color',
        'accent_color',
        'font_family',
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

    /**
     * Get the taxes for the store
     */
    public function taxes(): HasMany
    {
        return $this->hasMany(StoreTax::class);
    }

    /**
     * Get enabled taxes
     */
    public function enabledTaxes(): HasMany
    {
        return $this->hasMany(StoreTax::class)->where('is_enabled', true);
    }

    /**
     * Get tax settings
     */
    public function taxSettings(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(StoreTaxSetting::class);
    }

    /**
     * Get or create tax settings
     */
    public function getOrCreateTaxSettings(): StoreTaxSetting
    {
        return $this->taxSettings ?? StoreTaxSetting::create([
            'store_id' => $this->id,
            'taxes_enabled' => false,
            'tax_type' => 'order_level',
        ]);
    }

    /**
     * Get active subscription
     */
    public function activeSubscription(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Subscription::class)->where(function ($query) {
            $query->where('status', 'active')
                ->orWhere(function ($q) {
                    $q->where('status', 'trial')
                        ->where('trial_ends_at', '>', now());
                });
        });
    }

    /**
     * Get all subscriptions
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get cash register sessions
     */
    public function cashRegisterSessions(): HasMany
    {
        return $this->hasMany(CashRegisterSession::class);
    }

    /**
     * Check if store has access to a feature
     */
    public function hasFeature(string $featureSlug): bool
    {
        // Get the active subscription with plan relation loaded
        $subscription = $this->activeSubscription()->with('plan')->first();
        
        if (!$subscription) {
            // If store has no active subscription, check if any plans exist in the system
            // If plans exist, the subscription system is active and features should be restricted
            $plansExist = Plan::where('is_active', true)->exists();
            if ($plansExist) {
                return false; // No subscription = no access to premium features
            }
            return true; // No plans in system = subscription system not in use, allow all
        }
        
        return $subscription->hasFeature($featureSlug);
    }

    /**
     * Calculate taxes for a given amount
     */
    public function calculateTaxes(float $amount): array
    {
        $taxSettings = $this->taxSettings;
        if (!$taxSettings || !$taxSettings->taxes_enabled) {
            return ['taxes' => [], 'total_tax' => 0];
        }

        $taxes = [];
        $totalTax = 0;

        foreach ($this->enabledTaxes as $tax) {
            $taxAmount = $tax->calculateTax($amount);
            $taxes[] = [
                'id' => $tax->id,
                'name' => $tax->name,
                'percentage' => $tax->percentage,
                'amount' => $taxAmount,
            ];
            $totalTax += $taxAmount;
        }

        return ['taxes' => $taxes, 'total_tax' => $totalTax];
    }
}
