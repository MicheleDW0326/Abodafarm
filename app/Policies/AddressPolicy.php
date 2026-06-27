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
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Address $address): bool
    {
        return $user->id === $address->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Address $address): bool
    {
        return $user->id === $address->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Address $address): bool
    {
        // Cannot delete default address if it's the only one
        if ($address->is_default) {
            $activeCount = Address::where('user_id', $user->id)
                ->where('is_active', true)
                ->count();

            if ($activeCount <= 1) {
                return false;
            }
        }

        return $user->id === $address->user_id;
    }

    /**
     * Determine whether the user can set the model as default.
     */
    public function setDefault(User $user, Address $address): bool
    {
        return $user->id === $address->user_id;
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

        return $user->id === $address->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Address $address): bool
    {
        return $user->id === $address->user_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Address $address): bool
    {
        return false; // Never allow force delete via policy
    }
}
