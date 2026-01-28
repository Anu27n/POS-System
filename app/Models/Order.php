<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'store_customer_id',
        'store_id',
        'subtotal',
        'tax',
        'discount',
        'total',
        'payment_method',
        'payment_status',
        'order_status',
        'verification_code',
        'verification_qr_path',
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

    /**
     * Accessor for total_amount (alias for total)
     */
    public function getTotalAmountAttribute(): float
    {
        return (float) $this->total;
    }

    /**
     * Accessor for tax_amount (alias for tax)
     */
    public function getTaxAmountAttribute(): float
    {
        return (float) $this->tax;
    }

    /**
     * Accessor for discount_amount (alias for discount)
     */
    public function getDiscountAmountAttribute(): float
    {
        return (float) $this->discount;
    }

    /**
     * Accessor for status (alias for order_status)
     */
    public function getStatusAttribute(): string
    {
        return $this->order_status;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-' . strtoupper(Str::random(8));
            }
            if (empty($order->verification_code)) {
                // Generate a secure cryptographic token that cannot be guessed
                $order->verification_code = hash('sha256', Str::uuid() . microtime(true) . random_bytes(32));
            }
        });
    }

    /**
     * Get the QR code image URL
     */
    public function getQrCodeUrlAttribute(): ?string
    {
        if (!$this->verification_qr_path) {
            return null;
        }
        return Storage::disk('public')->url($this->verification_qr_path);
    }

    /**
     * Get the QR code as a data URI for inline display
     */
    public function getQrCodeDataUriAttribute(): ?string
    {
        if (!$this->verification_qr_path || !Storage::disk('public')->exists($this->verification_qr_path)) {
            return null;
        }
        // The QR file contains the base64 data URI string directly
        $content = Storage::disk('public')->get($this->verification_qr_path);
        // If it's already a data URI, return as-is; otherwise encode it
        if (str_starts_with($content, 'data:image/')) {
            return $content;
        }
        return 'data:image/png;base64,' . base64_encode($content);
    }

    /**
     * Check if QR code exists
     */
    public function hasQrCode(): bool
    {
        return $this->verification_qr_path && Storage::disk('public')->exists($this->verification_qr_path);
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

        // Add transaction to cash register if session is open (for online payments)
        if ($this->payment_method === 'online') {
            $cashSession = CashRegisterSession::getAnyOpenSession($this->store_id);
            if ($cashSession) {
                $cashSession->addTransaction('sale', 'online', $this->total, $this->id);
            }
        }
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
     * Alias for customer relationship
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the store customer (for POS walk-in customers)
     */
    public function storeCustomer(): BelongsTo
    {
        return $this->belongsTo(StoreCustomer::class, 'store_customer_id');
    }

    /**
     * Get customer name from either user or store customer
     */
    public function getCustomerNameAttribute(): string
    {
        if ($this->storeCustomer) {
            return $this->storeCustomer->name;
        }
        if ($this->user) {
            return $this->user->name;
        }
        return 'Walk-in Customer';
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
     * Get the order taxes
     */
    public function taxes(): HasMany
    {
        return $this->hasMany(OrderTax::class);
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
