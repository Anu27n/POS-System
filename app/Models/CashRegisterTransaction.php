<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashRegisterTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'cash_register_session_id',
        'order_id',
        'type',
        'payment_method',
        'amount',
        'description',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    const TYPES = [
        'sale' => 'Sale',
        'refund' => 'Refund',
        'cash_in' => 'Cash In',
        'cash_out' => 'Cash Out',
    ];

    /**
     * Get the session
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(CashRegisterSession::class, 'cash_register_session_id');
    }

    /**
     * Get the order
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
