<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'type',
        'expiration_date',
        'file_path',
    ];

    protected function casts(): array
    {
        return [
            'expiration_date' => 'date',
        ];
    }

    /**
     * Get the vehicle that owns this document.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Scope to get documents expiring soon.
     */
    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('expiration_date', '<=', now()->addDays($days));
    }

    /**
     * Scope to get expired documents.
     */
    public function scopeExpired($query)
    {
        return $query->where('expiration_date', '<', now());
    }

    /**
     * Check if document is expired.
     */
    public function isExpired(): bool
    {
        return $this->expiration_date < now();
    }

    /**
     * Check if document is expiring soon.
     */
    public function isExpiringSoon($days = 30): bool
    {
        return $this->expiration_date <= now()->addDays($days);
    }
}
