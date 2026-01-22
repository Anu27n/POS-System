<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class CashRegisterSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'staff_id',
        'user_id',
        'staff_name',
        'opening_cash',
        'closing_cash',
        'expected_cash',
        'cash_difference',
        'total_transactions',
        'total_cash_sales',
        'total_card_sales',
        'total_upi_sales',
        'total_other_sales',
        'notes',
        'closing_notes',
        'status',
        'opened_at',
        'closed_at',
    ];

    protected $casts = [
        'opening_cash' => 'decimal:2',
        'closing_cash' => 'decimal:2',
        'expected_cash' => 'decimal:2',
        'cash_difference' => 'decimal:2',
        'total_cash_sales' => 'decimal:2',
        'total_card_sales' => 'decimal:2',
        'total_upi_sales' => 'decimal:2',
        'total_other_sales' => 'decimal:2',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    /**
     * Get the store
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the staff member
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    /**
     * Get transactions
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(CashRegisterTransaction::class);
    }

    /**
     * Check if session is open
     */
    public function isOpen(): bool
    {
        return $this->closed_at === null;
    }

    /**
     * Get total sales
     */
    public function getTotalSalesAttribute(): float
    {
        return $this->total_cash_sales + $this->total_card_sales + $this->total_upi_sales + $this->total_other_sales;
    }

    /**
     * Calculate expected cash (opening + cash sales)
     */
    public function calculateExpectedCash(): float
    {
        return $this->opening_cash + $this->total_cash_sales;
    }

    /**
     * Add a transaction to the session
     */
    public function addTransaction(string $type, string $paymentMethod, float $amount, ?int $orderId = null, ?string $notes = null): CashRegisterTransaction
    {
        $transaction = $this->transactions()->create([
            'order_id' => $orderId,
            'type' => $type,
            'payment_method' => $paymentMethod,
            'amount' => $amount,
            'notes' => $notes,
        ]);

        // Update session totals based on transaction type
        if ($type === 'sale') {
            $this->total_transactions++;
            match (strtolower($paymentMethod)) {
                'cash' => $this->total_cash_sales += $amount,
                'card' => $this->total_card_sales += $amount,
                'upi' => $this->total_upi_sales += $amount,
                default => $this->total_other_sales += $amount,
            };
        } elseif ($type === 'cash_in') {
            // Cash in increases expected cash
            $this->total_cash_sales += $amount;
        } elseif ($type === 'cash_out') {
            // Cash out decreases expected cash
            $this->total_cash_sales -= $amount;
        } elseif ($type === 'refund' && strtolower($paymentMethod) === 'cash') {
            // Refunds decrease cash
            $this->total_cash_sales -= $amount;
        }
        
        $this->save();

        return $transaction;
    }

    /**
     * Close the session
     */
    public function closeSession(float $closingCash, ?string $notes = null): void
    {
        $this->expected_cash = $this->calculateExpectedCash();
        $this->closing_cash = $closingCash;
        $this->cash_difference = $closingCash - $this->expected_cash;
        $this->closing_notes = $notes;
        $this->closed_at = now();
        $this->save();
    }

    /**
     * Get last closed session for store
     */
    public static function getLastClosedSession(int $storeId): ?self
    {
        return static::where('store_id', $storeId)
            ->whereNotNull('closed_at')
            ->latest('closed_at')
            ->first();
    }

    /**
     * Get today's open session for user
     */
    public static function getTodayOpenSession(int $storeId, int $userId): ?self
    {
        return static::where('store_id', $storeId)
            ->where('staff_id', $userId)
            ->whereNull('closed_at')
            ->whereDate('opened_at', today())
            ->first();
    }

    /**
     * Get any open session for store
     */
    public static function getAnyOpenSession(int $storeId): ?self
    {
        return static::where('store_id', $storeId)
            ->whereNull('closed_at')
            ->latest('opened_at')
            ->first();
    }

    /**
     * Scope for open sessions
     */
    public function scopeOpen($query)
    {
        return $query->whereNull('closed_at');
    }

    /**
     * Scope for closed sessions
     */
    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }
}
