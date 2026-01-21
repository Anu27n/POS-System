<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'address',
        'is_active',
        'staff_id',
        'works_at_store_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is store owner
     */
    public function isStoreOwner(): bool
    {
        return $this->role === 'store_owner';
    }

    /**
     * Check if user is customer
     */
    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    /**
     * Get the store owned by the user
     */
    public function store(): HasOne
    {
        return $this->hasOne(Store::class);
    }

    /**
     * Get the orders for the user
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the cart for the user
     */
    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    /**
     * Get the staff profile for the user
     */
    public function staffProfile(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    /**
     * Get the store where the user works as staff
     */
    public function worksAtStore(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Store::class, 'works_at_store_id');
    }

    /**
     * Check if user is staff
     */
    public function isStaff(): bool
    {
        return $this->role === 'staff' && $this->staff_id !== null;
    }

    /**
     * Check if staff has a specific permission
     */
    public function hasStaffPermission(string $permission): bool
    {
        if (!$this->isStaff() || !$this->staffProfile) {
            return false;
        }
        return $this->staffProfile->hasPermission($permission);
    }

    /**
     * Check if staff has any of the given permissions
     */
    public function hasAnyStaffPermission(array $permissions): bool
    {
        if (!$this->isStaff() || !$this->staffProfile) {
            return false;
        }
        return $this->staffProfile->hasAnyPermission($permissions);
    }

    /**
     * Get the effective store for this user (owned or works at)
     */
    public function getEffectiveStore(): ?Store
    {
        if ($this->isStoreOwner()) {
            return $this->store;
        }
        if ($this->isStaff()) {
            return $this->worksAtStore;
        }
        return null;
    }
}
