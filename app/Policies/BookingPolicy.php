<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class BookingPolicy
{
    /**
     * Determine whether the user can view any bookings.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('orders.view');
    }

    /**
     * Determine whether the user can view a booking.
     */
    public function view(User $user, Order $booking): bool
    {
        // Booking is an inquiry order
        if (!$booking->isInquiry()) {
            return false;
        }

        // Owner can view their own booking
        if ($booking->user_id === $user->id) {
            return true;
        }

        // Admin/Staff/Farmer can view bookings
        return $user->can('orders.view');
    }

    /**
     * Determine whether the user can create a booking.
     */
    public function create(User $user): bool
    {
        return $user->can('orders.create');
    }

    /**
     * Determine whether the user can update a booking.
     */
    public function update(User $user, Order $booking): bool
    {
        if (!$booking->isInquiry()) {
            return false;
        }

        // Owner can update their own pending booking
        if ($booking->user_id === $user->id && $user->can('orders.update-own')) {
            return true;
        }

        return $user->can('orders.update');
    }

    /**
     * Determine whether the user can approve a booking.
     */
    public function approve(User $user, Order $booking): bool
    {
        if (!$booking->isInquiry()) {
            return false;
        }

        return $user->can('orders.approve');
    }

    /**
     * Determine whether the user can reject a booking.
     */
    public function reject(User $user, Order $booking): bool
    {
        if (!$booking->isInquiry()) {
            return false;
        }

        return $user->can('orders.reject');
    }

    /**
     * Determine whether the user can cancel a booking.
     */
    public function cancel(User $user, Order $booking): bool
    {
        if (!$booking->isInquiry()) {
            return false;
        }

        return $user->can('orders.cancel');
    }

    /**
     * Determine whether the user can delete a booking.
     */
    public function delete(User $user, Order $booking): bool
    {
        if (!$booking->isInquiry()) {
            return false;
        }

        return $user->can('orders.delete');
    }
}
