<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderTax extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'store_tax_id',
        'tax_name',
        'tax_percentage',
        'taxable_amount',
        'tax_amount',
    ];

    protected $casts = [
        'tax_percentage' => 'decimal:2',
        'taxable_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
    ];

    /**
     * Get the order
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the store tax
     */
    public function storeTax(): BelongsTo
    {
        return $this->belongsTo(StoreTax::class);
    }
}
