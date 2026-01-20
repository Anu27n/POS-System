<?php

if (!function_exists('formatCurrency')) {
    /**
     * Format amount with currency symbol
     */
    function formatCurrency($amount, $currency = 'INR')
    {
        $symbols = [
            'USD' => '$',
            'INR' => '₹',
            'EUR' => '€',
            'GBP' => '£',
        ];

        $symbol = $symbols[$currency] ?? $currency;
        return $symbol . number_format($amount, 2);
    }
}
