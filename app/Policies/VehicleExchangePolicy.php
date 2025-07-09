<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VehicleExchange;

class VehicleExchangePolicy
{
    /**
     * Determine whether the user can view any exchanges.
     */
    public function viewAny(User $user): bool
    {
        return true; // Both admin and chauffeur can view exchanges
    }

    /**
     * Determine whether the user can view the exchange.
     */
    public function view(User $user, VehicleExchange $exchange): bool
    {
        // Admin can view all, chauffeur can only view their own exchanges
        return $user->isAdmin() ||
            $exchange->from_driver_id === $user->id ||
            $exchange->to_driver_id === $user->id;
    }

    /**
     * Determine whether the user can create exchanges.
     */
    public function create(User $user): bool
    {
        return $user->isChauffeur(); // Only chauffeurs can create exchanges
    }

    /**
     * Determine whether the user can update the exchange.
     */
    public function update(User $user, VehicleExchange $exchange): bool
    {
        // Only the initiator can update and only if pending
        return $exchange->from_driver_id === $user->id && $exchange->isPending();
    }

    /**
     * Determine whether the user can delete the exchange.
     */
    public function delete(User $user, VehicleExchange $exchange): bool
    {
        // Admin can delete any, initiator can delete if pending
        return $user->isAdmin() ||
            ($exchange->from_driver_id === $user->id && $exchange->isPending());
    }

    /**
     * Determine whether the user can approve the exchange.
     */
    public function approve(User $user, VehicleExchange $exchange): bool
    {
        return $user->isAdmin() && $exchange->isPending();
    }

    /**
     * Determine whether the user can reject the exchange.
     */
    public function reject(User $user, VehicleExchange $exchange): bool
    {
        return $user->isAdmin() && $exchange->isPending();
    }
}
