<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleExchange extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_driver_id',
        'to_driver_id',
        'vehicle_id',
        'request_date',
        'status',
        'before_photo_path',
        'after_photo_path',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'request_date' => 'datetime',
        ];
    }

    /**
     * Get the driver who initiated the exchange.
     */
    public function fromDriver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_driver_id');
    }

    /**
     * Get the driver who will receive the vehicle.
     */
    public function toDriver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_driver_id');
    }

    /**
     * Get the vehicle being exchanged.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Scope to get pending exchanges.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get approved exchanges.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope to get rejected exchanges.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Approve the exchange.
     */
    public function approve(): void
    {
        $this->update(['status' => 'approved']);
    }

    /**
     * Reject the exchange.
     */
    public function reject(): void
    {
        $this->update(['status' => 'rejected']);
    }

    /**
     * Check if exchange is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if exchange is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if exchange is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
