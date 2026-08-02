<?php

namespace App\Policies;

use App\Models\FarmProfile;
use App\Models\User;

class FarmProfilePolicy
{
    /**
     * Determine whether the user can view any farm profiles.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('farm-profiles.view');
    }

    /**
     * Determine whether the user can view a farm profile.
     */
    public function view(User $user, FarmProfile $farmProfile): bool
    {
        // Farm profiles are public
        return true;
    }

    /**
     * Determine whether the user can create a farm profile.
     */
    public function create(User $user): bool
    {
        return $user->can('farm-profiles.create');
    }

    /**
     * Determine whether the user can update a farm profile.
     */
    public function update(User $user, FarmProfile $farmProfile): bool
    {
        // Owner can update their own profile
        if ($user->id === $farmProfile->user_id && $user->can('farm-profiles.update-own')) {
            return true;
        }

        return $user->can('farm-profiles.update');
    }

    /**
     * Determine whether the user can delete a farm profile.
     */
    public function delete(User $user, FarmProfile $farmProfile): bool
    {
        // Owner can delete their own profile
        if ($user->id === $farmProfile->user_id && $user->can('farm-profiles.delete')) {
            return true;
        }

        return $user->can('farm-profiles.delete');
    }
}
