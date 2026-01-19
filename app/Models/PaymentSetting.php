<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'gateway',
        'display_name',
        'credentials',
        'is_active',
        'is_test_mode',
    ];

    protected $casts = [
        'credentials' => 'encrypted:array',
        'is_active' => 'boolean',
        'is_test_mode' => 'boolean',
    ];

    /**
     * Get active payment gateway
     */
    public static function getActiveGateway(): ?self
    {
        return self::where('is_active', true)->first();
    }

    /**
     * Get gateway by name
     */
    public static function getGateway(string $gateway): ?self
    {
        return self::where('gateway', $gateway)->first();
    }
}
