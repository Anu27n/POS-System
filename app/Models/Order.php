<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'store_id',
        'subtotal',
        'tax',
        'discount',
        'total',
        'payment_method',
        'payment_status',
        'order_status',
        'verification_code',
        'verification_qr',
        'transaction_id',
        'notes',
        'paid_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-' . strtoupper(Str::random(8));
            }
            if (empty($order->verification_code)) {
                $order->verification_code = strtoupper(Str::random(12));
            }
        });
    }

    /**
     * Check if order is paid
     */
    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    /**
     * Check if order is completed
     */
    public function isCompleted(): bool
    {
        return $this->order_status === 'completed';
    }

    /**
     * Check if order is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->order_status === 'cancelled';
    }

    /**
     * Mark order as paid
     */
    public function markAsPaid(?string $transactionId = null): void
    {
        $this->update([
            'payment_status' => 'paid',
            'transaction_id' => $transactionId,
            'paid_at' => now(),
        ]);
    }

    /**
     * Cancel order and restore stock
     */
    public function cancel(): void
    {
        // Restore stock for each item
        foreach ($this->items as $item) {
            $item->product->restoreStock($item->quantity);
        }

        $this->update([
            'order_status' => 'cancelled',
        ]);
    }

    /**
     * Get the customer who placed the order
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the store for the order
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get the order items
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Scope to get orders by status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('order_status', $status);
    }

    /**
     * Scope to get paid orders
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    /**
     * Scope to get unpaid orders
     */
    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', 'pending');
    }
}
