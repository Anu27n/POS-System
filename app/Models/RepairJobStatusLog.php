<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RepairJobStatusLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'repair_job_id',
        'old_status',
        'new_status',
        'changed_by_id',
        'notes',
        'notify_customer',
        'notification_sent_at',
    ];

    protected $casts = [
        'notify_customer' => 'boolean',
        'notification_sent_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function repairJob(): BelongsTo
    {
        return $this->belongsTo(RepairJob::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_id');
    }

    /**
     * Get the old status label
     */
    public function getOldStatusLabelAttribute(): ?string
    {
        if (!$this->old_status) {
            return null;
        }
        return RepairJob::STATUSES[$this->old_status]['label'] ?? ucfirst($this->old_status);
    }

    /**
     * Get the new status label
     */
    public function getNewStatusLabelAttribute(): string
    {
        return RepairJob::STATUSES[$this->new_status]['label'] ?? ucfirst($this->new_status);
    }

    /**
     * Get the new status color
     */
    public function getNewStatusColorAttribute(): string
    {
        return RepairJob::STATUSES[$this->new_status]['color'] ?? 'secondary';
    }

    /**
     * Check if notification was sent
     */
    public function wasNotificationSent(): bool
    {
        return $this->notify_customer && $this->notification_sent_at !== null;
    }
}
