<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreCustomer extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'user_id',
        'name',
        'email',
        'phone',
        'address',
        'notes',
        'total_spent',
        'total_orders',
        'last_order_at',
        'is_manually_added',
    ];

    protected $casts = [
        'total_spent' => 'decimal:2',
        'total_orders' => 'integer',
        'last_order_at' => 'datetime',
        'is_manually_added' => 'boolean',
    ];

    /**
     * Get the store that the customer belongs to
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get the associated user account
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Update customer stats after order
     */
    public function recordOrder(float $amount): void
    {
        $this->total_spent = ($this->total_spent ?? 0) + $amount;
        $this->total_orders = ($this->total_orders ?? 0) + 1;
        $this->last_order_at = now();
        $this->save();
    }

    /**
     * Find or create customer for store from user
     */
    public static function findOrCreateFromUser(Store $store, User $user): self
    {
        return self::firstOrCreate(
            [
                'store_id' => $store->id,
                'user_id' => $user->id,
            ],
            [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ]
        );
    }

    /**
     * Find or create customer for store by phone/email
     */
    public static function findOrCreateFromDetails(Store $store, array $details): self
    {
        // First try to find by email or phone
        $query = self::where('store_id', $store->id);
        
        if (!empty($details['email'])) {
            $existing = $query->where('email', $details['email'])->first();
            if ($existing) {
                $existing->update([
                    'name' => $details['name'] ?? $existing->name,
                    'phone' => $details['phone'] ?? $existing->phone,
                ]);
                return $existing;
            }
        }

        if (!empty($details['phone'])) {
            $existing = self::where('store_id', $store->id)
                ->where('phone', $details['phone'])
                ->first();
            if ($existing) {
                $existing->update([
                    'name' => $details['name'] ?? $existing->name,
                    'email' => $details['email'] ?? $existing->email,
                ]);
                return $existing;
            }
        }

        // Create new customer
        return self::create([
            'store_id' => $store->id,
            'name' => $details['name'] ?? 'Guest',
            'email' => $details['email'] ?? null,
            'phone' => $details['phone'] ?? null,
            'is_manually_added' => false,
        ]);
    }
}
