<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Maintenance extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'maintenance_type',
        'description',
        'date',
        'reminder_date',
        'invoice_path',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'reminder_date' => 'date',
        ];
    }

    /**
     * Get the vehicle that owns this maintenance.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Scope to get upcoming maintenance reminders.
     */
    public function scopeUpcoming($query, $days = 7)
    {
        return $query->whereNotNull('reminder_date')
            ->where('reminder_date', '<=', now()->addDays($days))
            ->where('reminder_date', '>=', now());
    }

    /**
     * Scope to get overdue maintenance.
     */
    public function scopeOverdue($query)
    {
        return $query->whereNotNull('reminder_date')
            ->where('reminder_date', '<', now());
    }

    /**
     * Check if maintenance reminder is due.
     */
    public function isReminderDue(): bool
    {
        return $this->reminder_date && $this->reminder_date <= now();
    }

    /**
     * Check if maintenance reminder is upcoming.
     */
    public function isReminderUpcoming($days = 7): bool
    {
        return $this->reminder_date &&
            $this->reminder_date <= now()->addDays($days) &&
            $this->reminder_date >= now();
    }
}
