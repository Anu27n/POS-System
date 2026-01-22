<?php

namespace App\Helpers;

class CurrencyHelper
{
    /**
     * Get currency symbol
     */
    public static function getCurrencySymbol($currency = 'INR')
    {
        $symbols = [
            'USD' => '$',
            'INR' => '₹',
            'EUR' => '€',
            'GBP' => '£',
        ];

        return $symbols[$currency] ?? $currency;
    }

    /**
     * Format amount with currency symbol
     */
    public static function format($amount, $currency = 'INR')
    {
        $symbol = self::getCurrencySymbol($currency);
        return $symbol . number_format((float)$amount, 2);
    }
}
