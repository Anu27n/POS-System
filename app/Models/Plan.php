<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'billing_cycle',
        'trial_days',
        'features',
        'is_popular',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'features' => 'array',
        'is_popular' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($plan) {
            if (empty($plan->slug)) {
                $plan->slug = Str::slug($plan->name);
            }
        });
    }

    /**
     * Get subscriptions for this plan
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Check if plan has a specific feature
     */
    public function hasFeature(string $featureSlug): bool
    {
        return in_array($featureSlug, $this->features ?? []);
    }

    /**
     * Get all available features
     */
    public function getAvailableFeatures()
    {
        return PlanFeature::whereIn('slug', $this->features ?? [])->get();
    }

    /**
     * Get price display with cycle
     */
    public function getPriceDisplayAttribute(): string
    {
        $cycle = match($this->billing_cycle) {
            'monthly' => '/month',
            'quarterly' => '/quarter',
            'yearly' => '/year',
            default => '',
        };
        return '₹' . number_format($this->price, 2) . $cycle;
    }

    /**
     * Scope for active plans
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
