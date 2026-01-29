<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

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
        'is_active' => 'boolean',
        'is_test_mode' => 'boolean',
    ];

    /**
     * Get credentials attribute - decrypt and decode JSON
     */
    public function getCredentialsAttribute($value)
    {
        if (empty($value)) {
            return [];
        }

        try {
            // Try to decrypt first (for encrypted values)
            $decrypted = Crypt::decryptString($value);
            return json_decode($decrypted, true) ?? [];
        } catch (\Exception $e) {
            // If decryption fails, try to decode as plain JSON (for old unencrypted data)
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
            // Return empty array if all else fails
            return [];
        }
    }

    /**
     * Set credentials attribute - encode JSON and encrypt
     */
    public function setCredentialsAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['credentials'] = null;
            return;
        }

        $json = is_array($value) ? json_encode($value) : $value;
        $this->attributes['credentials'] = Crypt::encryptString($json);
    }

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
