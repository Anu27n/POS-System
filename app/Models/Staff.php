<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Staff extends Model
{
    use HasFactory;

    protected $table = 'staff';

    protected $fillable = [
        'store_id',
        'user_id',
        'name',
        'email',
        'phone',
        'role',
        'permissions',
        'is_active',
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Available staff roles
     */
    public const ROLES = [
        'cashier' => 'Cashier',
        'manager' => 'Manager',
        'inventory_manager' => 'Inventory Manager',
        'supervisor' => 'Supervisor',
        'technician' => 'Technician',
        'senior_technician' => 'Senior Technician',
    ];

    /**
     * Available permissions for RBAC
     */
    public const PERMISSIONS = [
        'view_dashboard' => 'View Dashboard',
        'manage_products' => 'Manage Products',
        'manage_categories' => 'Manage Categories',
        'manage_inventory' => 'Manage Inventory',
        'view_orders' => 'View Orders',
        'manage_orders' => 'Manage Orders',
        'use_pos' => 'Use POS Terminal',
        'process_payments' => 'Process Payments',
        'view_customers' => 'View Customers',
        'manage_customers' => 'Manage Customers',
        'view_reports' => 'View Reports',
        'manage_staff' => 'Manage Staff',
        'manage_settings' => 'Manage Store Settings',
        // Repair-specific permissions
        'view_repair_jobs' => 'View Repair Jobs',
        'manage_repair_jobs' => 'Manage Repair Jobs',
        'view_assigned_jobs' => 'View Assigned Jobs',
        'update_job_status' => 'Update Job Status',
        'add_repair_parts' => 'Add Parts to Jobs',
        'create_repair_invoice' => 'Create Repair Invoice',
    ];

    /**
     * Default permissions per role
     */
    public const ROLE_PERMISSIONS = [
        'cashier' => [
            'view_dashboard',
            'view_orders',
            'use_pos',
            'process_payments',
            'view_customers',
            'view_repair_jobs',
        ],
        'inventory_manager' => [
            'view_dashboard',
            'manage_products',
            'manage_categories',
            'manage_inventory',
            'view_orders',
            'view_reports',
            'view_repair_jobs',
        ],
        'technician' => [
            'view_dashboard',
            'view_assigned_jobs',
            'update_job_status',
            'add_repair_parts',
            'view_customers',
        ],
        'senior_technician' => [
            'view_dashboard',
            'view_repair_jobs',
            'manage_repair_jobs',
            'view_assigned_jobs',
            'update_job_status',
            'add_repair_parts',
            'view_customers',
            'manage_inventory',
        ],
        'supervisor' => [
            'view_dashboard',
            'manage_products',
            'manage_categories',
            'manage_inventory',
            'view_orders',
            'manage_orders',
            'use_pos',
            'process_payments',
            'view_customers',
            'manage_customers',
            'view_reports',
            'view_repair_jobs',
            'manage_repair_jobs',
            'create_repair_invoice',
        ],
        'manager' => [
            'view_dashboard',
            'manage_products',
            'manage_categories',
            'manage_inventory',
            'view_orders',
            'manage_orders',
            'use_pos',
            'process_payments',
            'view_customers',
            'manage_customers',
            'view_reports',
            'manage_staff',
            'manage_settings',
            'view_repair_jobs',
            'manage_repair_jobs',
            'create_repair_invoice',
        ],
    ];

    /**
     * Get the store that the staff belongs to
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
     * Check if staff has a specific permission
     */
    public function hasPermission(string $permission): bool
    {
        $permissions = $this->permissions ?? self::ROLE_PERMISSIONS[$this->role] ?? [];
        return in_array($permission, $permissions);
    }

    /**
     * Check if staff has any of the given permissions
     */
    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if staff has all of the given permissions
     */
    public function hasAllPermissions(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get effective permissions (custom or role defaults)
     */
    public function getEffectivePermissions(): array
    {
        return $this->permissions ?? self::ROLE_PERMISSIONS[$this->role] ?? [];
    }

    /**
     * Get role display name
     */
    public function getRoleNameAttribute(): string
    {
        return self::ROLES[$this->role] ?? ucfirst($this->role);
    }

    /**
     * Check if staff is a technician
     */
    public function isTechnician(): bool
    {
        return in_array($this->role, ['technician', 'senior_technician']);
    }

    /**
     * Get assigned repair jobs
     */
    public function assignedRepairJobs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\RepairJob::class, 'assigned_technician_id');
    }

    /**
     * Get open assigned repair jobs
     */
    public function openRepairJobs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->assignedRepairJobs()
            ->whereNotIn('status', ['delivered', 'cancelled', 'unrepairable']);
    }

    /**
     * Get completed repair jobs count
     */
    public function completedJobsCount(): int
    {
        return $this->assignedRepairJobs()
            ->where('status', 'delivered')
            ->count();
    }
}
