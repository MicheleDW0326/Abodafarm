<?php

namespace App\Policies;

use App\Models\Address;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AddressPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Anyone with addresses.view permission can list addresses
        return $user->can('addresses.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Address $address): bool
    {
        // Admin/Staff can view any address
        if ($user->can('addresses.view')) {
            return true;
        }

        // Users can only view their own addresses
        return $user->can('addresses.view-own') && $user->id === $address->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('addresses.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Address $address): bool
    {
        // Admin/Staff can update any address
        if ($user->can('addresses.update')) {
            return true;
        }

        // Users can only update their own addresses
        return $user->can('addresses.update-own') && $user->id === $address->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Address $address): bool
    {
        // Cannot delete default address if it's the only one
        if ($address->is_default) {
            $activeCount = Address::query()
                ->where('user_id', $user->id)
                ->where('is_active', true)
                ->count();

            if ($activeCount <= 1) {
                return false;
            }
        }

        // Admin/Staff can delete any address
        if ($user->can('addresses.delete')) {
            return true;
        }

        // Users can only delete their own addresses
        return $user->can('addresses.delete-own') && $user->id === $address->user_id;
    }

    /**
     * Determine whether the user can set the model as default.
     */
    public function setDefault(User $user, Address $address): bool
    {
        // Admin/Staff can set any address as default
        if ($user->can('addresses.update')) {
            return true;
        }

        // Users can only set their own addresses as default
        return $user->can('addresses.update-own') && $user->id === $address->user_id;
    }

    /**
     * Determine whether the user can toggle the active status.
     */
    public function toggleActive(User $user, Address $address): bool
    {
        // Cannot deactivate default address
        if ($address->is_default && !$address->is_active) {
            return false;
        }

        // Admin/Staff can toggle any address
        if ($user->can('addresses.update')) {
            return true;
        }

        // Users can only toggle their own addresses
        return $user->can('addresses.update-own') && $user->id === $address->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Address $address): bool
    {
        // Admin/Staff can restore any address
        if ($user->can('addresses.update')) {
            return true;
        }

        // Users can only restore their own addresses
        return $user->can('addresses.update-own') && $user->id === $address->user_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Address $address): bool
    {
        return false; // Never allow force delete via policy
    }
}
