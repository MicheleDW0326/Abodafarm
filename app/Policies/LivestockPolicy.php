<?php

namespace App\Policies;

use App\Models\Livestock;
use App\Models\User;

class LivestockPolicy
{
    /**
     * Determine whether the user can view any livestock.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('products.view');
    }

    /**
     * Determine whether the user can view a livestock.
     */
    public function view(User $user, Livestock $livestock): bool
    {
        return true; // Livestock/products are publicly viewable
    }

    /**
     * Determine whether the user can create a livestock.
     */
    public function create(User $user): bool
    {
        return $user->can('products.create');
    }

    /**
     * Determine whether the user can update a livestock.
     */
    public function update(User $user, Livestock $livestock): bool
    {
        // Owner can update their own livestock
        if ($livestock->owner_id === $user->id && $user->can('products.update-own')) {
            return true;
        }

        return $user->can('products.update');
    }

    /**
     * Determine whether the user can delete a livestock.
     */
    public function delete(User $user, Livestock $livestock): bool
    {
        // Owner can delete their own livestock
        if ($livestock->owner_id === $user->id && $user->can('products.delete-own')) {
            return true;
        }

        return $user->can('products.delete');
    }
}
