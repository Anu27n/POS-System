<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RepairJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'store_customer_id',
        'ticket_number',
        'device_type',
        'device_brand',
        'device_model',
        'imei_serial',
        'device_color',
        'device_password',
        'device_accessories',
        'issue_description',
        'diagnosis_notes',
        'repair_notes',
        'internal_notes',
        'status',
        'priority',
        'assigned_technician_id',
        'received_by_id',
        'estimated_cost',
        'final_cost',
        'advance_paid',
        'discount',
        'expected_delivery_at',
        'diagnosed_at',
        'repair_started_at',
        'repaired_at',
        'delivered_at',
        'warranty_days',
        'warranty_until',
        'order_id',
    ];

    protected $casts = [
        'device_accessories' => 'array',
        'estimated_cost' => 'decimal:2',
        'final_cost' => 'decimal:2',
        'advance_paid' => 'decimal:2',
        'discount' => 'decimal:2',
        'expected_delivery_at' => 'datetime',
        'diagnosed_at' => 'datetime',
        'repair_started_at' => 'datetime',
        'repaired_at' => 'datetime',
        'delivered_at' => 'datetime',
        'warranty_until' => 'date',
    ];

    /**
     * Device types
     */
    public const DEVICE_TYPES = [
        'phone' => 'Mobile Phone',
        'tablet' => 'Tablet',
        'laptop' => 'Laptop',
        'watch' => 'Smart Watch',
        'gaming_console' => 'Gaming Console',
        'other' => 'Other',
    ];

    /**
     * Status labels and colors
     */
    public const STATUSES = [
        'received' => ['label' => 'Received', 'color' => 'secondary', 'icon' => 'inbox'],
        'diagnosed' => ['label' => 'Diagnosed', 'color' => 'info', 'icon' => 'search'],
        'waiting_approval' => ['label' => 'Waiting Approval', 'color' => 'warning', 'icon' => 'clock'],
        'waiting_parts' => ['label' => 'Waiting for Parts', 'color' => 'warning', 'icon' => 'package'],
        'in_progress' => ['label' => 'In Progress', 'color' => 'primary', 'icon' => 'tool'],
        'repaired' => ['label' => 'Repaired', 'color' => 'success', 'icon' => 'check-circle'],
        'ready_pickup' => ['label' => 'Ready for Pickup', 'color' => 'success', 'icon' => 'gift'],
        'delivered' => ['label' => 'Delivered', 'color' => 'dark', 'icon' => 'check-square'],
        'cancelled' => ['label' => 'Cancelled', 'color' => 'danger', 'icon' => 'x-circle'],
        'unrepairable' => ['label' => 'Unrepairable', 'color' => 'danger', 'icon' => 'alert-triangle'],
    ];

    /**
     * Priority levels
     */
    public const PRIORITIES = [
        'low' => ['label' => 'Low', 'color' => 'secondary'],
        'normal' => ['label' => 'Normal', 'color' => 'primary'],
        'high' => ['label' => 'High', 'color' => 'warning'],
        'urgent' => ['label' => 'Urgent', 'color' => 'danger'],
    ];

    /**
     * Valid status transitions
     */
    public const STATUS_TRANSITIONS = [
        'received' => ['diagnosed', 'in_progress', 'cancelled'],
        'diagnosed' => ['waiting_approval', 'waiting_parts', 'in_progress', 'unrepairable', 'cancelled'],
        'waiting_approval' => ['waiting_parts', 'in_progress', 'cancelled'],
        'waiting_parts' => ['in_progress', 'cancelled'],
        'in_progress' => ['repaired', 'waiting_parts', 'unrepairable', 'cancelled'],
        'repaired' => ['ready_pickup'],
        'ready_pickup' => ['delivered'],
        'delivered' => [],
        'cancelled' => [],
        'unrepairable' => ['delivered', 'cancelled'],
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($job) {
            if (empty($job->ticket_number)) {
                $job->ticket_number = static::generateTicketNumber($job->store_id);
            }
        });
    }

    /**
     * Generate unique ticket number for a store
     */
    public static function generateTicketNumber(int $storeId): string
    {
        $prefix = 'REP';
        $year = date('Y');
        $month = date('m');
        
        // Get the last ticket number for this store this month
        $lastTicket = static::where('store_id', $storeId)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastTicket && preg_match('/(\d+)$/', $lastTicket->ticket_number, $matches)) {
            $sequence = intval($matches[1]) + 1;
        } else {
            $sequence = 1;
        }
        
        return sprintf('%s-%s%s-%05d', $prefix, $year, $month, $sequence);
    }

    /**
     * Check if status transition is valid
     */
    public function canTransitionTo(string $newStatus): bool
    {
        $allowedTransitions = self::STATUS_TRANSITIONS[$this->status] ?? [];
        return in_array($newStatus, $allowedTransitions);
    }

    /**
     * Update status with logging
     */
    public function updateStatus(string $newStatus, ?int $userId = null, ?string $notes = null, bool $notifyCustomer = false): bool
    {
        if (!$this->canTransitionTo($newStatus)) {
            return false;
        }

        $oldStatus = $this->status;
        
        // Update the job status
        $this->status = $newStatus;
        
        // Update relevant timestamps
        switch ($newStatus) {
            case 'diagnosed':
                $this->diagnosed_at = now();
                break;
            case 'in_progress':
                $this->repair_started_at = $this->repair_started_at ?? now();
                break;
            case 'repaired':
                $this->repaired_at = now();
                if ($this->warranty_days > 0) {
                    $this->warranty_until = now()->addDays($this->warranty_days)->toDateString();
                }
                break;
            case 'delivered':
                $this->delivered_at = now();
                break;
        }
        
        $this->save();
        
        // Log the status change
        $this->statusLogs()->create([
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'changed_by_id' => $userId,
            'notes' => $notes,
            'notify_customer' => $notifyCustomer,
        ]);
        
        return true;
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status]['label'] ?? ucfirst($this->status);
    }

    /**
     * Get status color
     */
    public function getStatusColorAttribute(): string
    {
        return self::STATUSES[$this->status]['color'] ?? 'secondary';
    }

    /**
     * Get priority label
     */
    public function getPriorityLabelAttribute(): string
    {
        return self::PRIORITIES[$this->priority]['label'] ?? ucfirst($this->priority);
    }

    /**
     * Get priority color
     */
    public function getPriorityColorAttribute(): string
    {
        return self::PRIORITIES[$this->priority]['color'] ?? 'secondary';
    }

    /**
     * Get device type label
     */
    public function getDeviceTypeLabelAttribute(): string
    {
        return self::DEVICE_TYPES[$this->device_type] ?? 'Unknown';
    }

    /**
     * Get full device name
     */
    public function getDeviceNameAttribute(): string
    {
        $parts = array_filter([
            $this->device_brand,
            $this->device_model,
        ]);
        
        return !empty($parts) ? implode(' ', $parts) : $this->device_type_label;
    }

    /**
     * Calculate total parts cost
     */
    public function getPartsCostAttribute(): float
    {
        return (float) $this->parts()->sum('total_price');
    }

    /**
     * Get balance due
     */
    public function getBalanceDueAttribute(): float
    {
        $total = $this->final_cost ?? $this->estimated_cost ?? 0;
        return max(0, $total - $this->advance_paid - $this->discount);
    }

    /**
     * Check if job is overdue
     */
    public function getIsOverdueAttribute(): bool
    {
        if (!$this->expected_delivery_at) {
            return false;
        }
        
        $openStatuses = ['received', 'diagnosed', 'waiting_approval', 'waiting_parts', 'in_progress'];
        
        return in_array($this->status, $openStatuses) && 
               $this->expected_delivery_at->isPast();
    }

    /**
     * Check if warranty is active
     */
    public function getIsUnderWarrantyAttribute(): bool
    {
        return $this->warranty_until && $this->warranty_until->isFuture();
    }

    /**
     * Relationships
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(StoreCustomer::class, 'store_customer_id');
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'assigned_technician_id');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'received_by_id');
    }

    public function parts(): HasMany
    {
        return $this->hasMany(RepairJobPart::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(RepairJobStatusLog::class)->orderBy('created_at', 'desc');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Scopes
     */
    public function scopeForStore($query, int $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    public function scopeOpen($query)
    {
        return $query->whereNotIn('status', ['delivered', 'cancelled', 'unrepairable']);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'delivered');
    }

    public function scopeDueToday($query)
    {
        return $query->whereDate('expected_delivery_at', today())
                     ->whereNotIn('status', ['delivered', 'cancelled', 'unrepairable']);
    }

    public function scopeOverdue($query)
    {
        return $query->where('expected_delivery_at', '<', now())
                     ->whereNotIn('status', ['delivered', 'cancelled', 'unrepairable']);
    }

    public function scopeAssignedTo($query, int $technicianId)
    {
        return $query->where('assigned_technician_id', $technicianId);
    }

    public function scopeWithStatus($query, string|array $status)
    {
        if (is_array($status)) {
            return $query->whereIn('status', $status);
        }
        return $query->where('status', $status);
    }
}
