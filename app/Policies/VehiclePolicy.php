<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vehicle;

class VehiclePolicy
{
    /**
     * Determine whether the user can view any vehicles.
     */
    public function viewAny(User $user): bool
    {
        return true; // Both admin and chauffeur can view vehicles
    }

    /**
     * Determine whether the user can view the vehicle.
     */
    public function view(User $user, Vehicle $vehicle): bool
    {
        // Admin can view all vehicles, chauffeur can only view their assigned vehicle
        return $user->isAdmin() || $user->vehicle_id === $vehicle->id;
    }

    /**
     * Determine whether the user can create vehicles.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the vehicle.
     */
    public function update(User $user, Vehicle $vehicle): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the vehicle.
     */
    public function delete(User $user, Vehicle $vehicle): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can archive the vehicle.
     */
    public function archive(User $user, Vehicle $vehicle): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the vehicle.
     */
    public function restore(User $user, Vehicle $vehicle): bool
    {
        return $user->isAdmin();
    }
}
