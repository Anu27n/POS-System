<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreTax extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'name',
        'percentage',
        'is_enabled',
        'sort_order',
    ];

    protected $casts = [
        'percentage' => 'decimal:2',
        'is_enabled' => 'boolean',
    ];

    /**
     * Get the store
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Scope for enabled taxes
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Calculate tax amount for a given value
     */
    public function calculateTax(float $amount): float
    {
        return round($amount * ($this->percentage / 100), 2);
    }
}
