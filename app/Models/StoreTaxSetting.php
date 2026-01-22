<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreTaxSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'taxes_enabled',
        'tax_type',
        'tax_number',
        'show_tax_on_receipt',
        'tax_inclusive_pricing',
    ];

    protected $casts = [
        'taxes_enabled' => 'boolean',
        'show_tax_on_receipt' => 'boolean',
        'tax_inclusive_pricing' => 'boolean',
    ];

    /**
     * Get the store
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Check if using item-level tax
     */
    public function isItemLevelTax(): bool
    {
        return $this->tax_type === 'item_level';
    }

    /**
     * Check if using order-level tax
     */
    public function isOrderLevelTax(): bool
    {
        return $this->tax_type === 'order_level';
    }
}
