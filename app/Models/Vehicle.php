<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Vehicle",
    title: "Vehicle",
    description: "Vehicle model",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "registration_number", type: "string", example: "ABC-123"),
        new OA\Property(property: "model", type: "string", example: "Toyota Camry"),
        new OA\Property(property: "year", type: "integer", example: 2022),
        new OA\Property(property: "status", type: "string", enum: ["active", "archived"], example: "active"),
        new OA\Property(property: "archived_at", type: "string", format: "date-time", nullable: true),
        new OA\Property(property: "created_at", type: "string", format: "date-time", example: "2024-01-01T00:00:00Z"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time", example: "2024-01-01T00:00:00Z")
    ]
)]

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_number',
        'model',
        'year',
        'status',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'archived_at' => 'datetime',
        ];
    }

    /**
     * Get the user assigned to this vehicle.
     */
    public function assignedUser(): HasOne
    {
        return $this->hasOne(User::class);
    }

    /**
     * Get the documents for this vehicle.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(VehicleDocument::class);
    }

    /**
     * Get the maintenances for this vehicle.
     */
    public function maintenances(): HasMany
    {
        return $this->hasMany(Maintenance::class);
    }

    /**
     * Get the exchanges for this vehicle.
     */
    public function exchanges(): HasMany
    {
        return $this->hasMany(VehicleExchange::class);
    }

    /**
     * Scope to get only active vehicles.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get only archived vehicles.
     */
    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    /**
     * Archive the vehicle.
     */
    public function archive(): void
    {
        $this->update([
            'status' => 'archived',
            'archived_at' => now(),
        ]);
    }

    /**
     * Restore the vehicle.
     */
    public function restore(): void
    {
        $this->update([
            'status' => 'active',
            'archived_at' => null,
        ]);
    }
}
