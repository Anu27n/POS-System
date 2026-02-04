<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RepairJobPart extends Model
{
    use HasFactory;

    protected $fillable = [
        'repair_job_id',
        'product_id',
        'quantity',
        'unit_price',
        'total_price',
        'added_by_id',
        'notes',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Calculate total price automatically
        static::creating(function ($part) {
            $part->total_price = $part->unit_price * $part->quantity;
        });

        static::updating(function ($part) {
            $part->total_price = $part->unit_price * $part->quantity;
        });

        // Deduct stock when part is added
        static::created(function ($part) {
            $product = $part->product;
            if ($product && $product->track_inventory) {
                $product->reduceStock($part->quantity);
            }
        });

        // Restore stock when part is removed
        static::deleted(function ($part) {
            $product = $part->product;
            if ($product && $product->track_inventory) {
                $product->restoreStock($part->quantity);
            }
        });
    }

    /**
     * Relationships
     */
    public function repairJob(): BelongsTo
    {
        return $this->belongsTo(RepairJob::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'added_by_id');
    }

    /**
     * Get the part name from the product
     */
    public function getPartNameAttribute(): string
    {
        return $this->product?->name ?? 'Unknown Part';
    }
}
