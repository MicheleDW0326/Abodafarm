<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Determine whether the user can view the order.
     */
    public function view(User $user, Order $order): bool
    {
        if ($user->can('orders.view')) {
            return true;
        }

        // Customer can view their own orders
        if ($user->can('orders.view-own') && $order->user_id === $user->id) {
            return true;
        }

        // Farmer can view orders containing their products
        if ($user->hasRole('peternak') && $user->can('orders.view')) {
            return $order->items()
                ->whereHas('product', fn($q) => $q->where('owner_id', $user->id))
                ->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can update the order.
     */
    public function update(User $user, Order $order): bool
    {
        if ($user->can('orders.update')) {
            return true;
        }

        // Customer can update their own orders
        if ($user->can('orders.update-own') && $order->user_id === $user->id) {
            return true;
        }

        // Farmer can update orders containing their products
        if ($user->hasRole('peternak') && $user->can('orders.update')) {
            return $order->items()
                ->whereHas('product', fn($q) => $q->where('owner_id', $user->id))
                ->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can update the order status.
     */
    public function updateStatus(User $user, Order $order): bool
    {
        return $user->can('orders.update');
    }

    /**
     * Determine whether the user can approve a booking.
     * Uses the new booking_status_id FK relationship.
     */
    public function approve(User $user, Order $order): bool
    {
        if (!$user->can('orders.approve')) {
            return false;
        }

        if (!$order->isInquiry()) {
            return false;
        }

        // Check using new FK relationship
        $currentStatus = $order->bookingStatusRelation?->code ?? $order->status;
        if ($currentStatus !== Order::STATUS_PENDING) {
            return false;
        }

        // Farmer can only approve orders containing their products
        if ($user->hasRole('peternak')) {
            return $order->items()
                ->whereHas('product', fn($q) => $q->where('owner_id', $user->id))
                ->exists();
        }

        return true;
    }

    /**
     * Determine whether the user can reject a booking.
     * Uses the new booking_status_id FK relationship.
     */
    public function reject(User $user, Order $order): bool
    {
        if (!$user->can('orders.reject')) {
            return false;
        }

        if (!$order->isInquiry()) {
            return false;
        }

        // Check using new FK relationship
        $currentStatus = $order->bookingStatusRelation?->code ?? $order->status;
        if ($currentStatus !== Order::STATUS_PENDING) {
            return false;
        }

        // Farmer can only reject orders containing their products
        if ($user->hasRole('peternak')) {
            return $order->items()
                ->whereHas('product', fn($q) => $q->where('owner_id', $user->id))
                ->exists();
        }

        return true;
    }

    /**
     * Determine whether the user can cancel the order.
     * Uses the new FK relationships for proper status checking.
     */
    public function cancel(User $user, Order $order): bool
    {
        if (!$user->can('orders.cancel')) {
            return false;
        }

        // Check current status using new architecture
        $currentStatus = $order->effective_status ?? $order->status;

        // Can only cancel if in pending state
        if ($currentStatus !== Order::STATUS_PENDING) {
            return false;
        }

        // Customer can only cancel their own orders
        if ($order->user_id === $user->id && $user->can('orders.cancel')) {
            return true;
        }

        // Admin/Staff can cancel any order
        return true;
    }

    /**
     * Determine whether the user can delete the order.
     */
    public function delete(User $user, Order $order): bool
    {
        return $user->can('orders.delete');
    }

    /**
     * Determine whether the user can view the order timeline.
     */
    public function viewTimeline(User $user, Order $order): bool
    {
        return $this->view($user, $order);
    }

    /**
     * Determine whether the user can process (start fulfillment) the order.
     */
    public function process(User $user, Order $order): bool
    {
        if (!$user->can('orders.process')) {
            return false;
        }

        // Farmer can only process approved orders containing their products
        if ($user->hasRole('peternak')) {
            $bookingStatus = $order->bookingStatusRelation?->code;
            if ($bookingStatus === 'approved') {
                return $order->items()
                    ->whereHas('product', fn($q) => $q->where('owner_id', $user->id))
                    ->exists();
            }
        }

        return true;
    }

    /**
     * Determine whether the user can ship the order.
     */
    public function ship(User $user, Order $order): bool
    {
        if (!$user->can('orders.ship')) {
            return false;
        }

        // Farmer can only ship orders containing their products
        if ($user->hasRole('peternak')) {
            $shipmentStatus = $order->shipmentStatusRelation?->code;
            if ($shipmentStatus === 'processing') {
                return $order->items()
                    ->whereHas('product', fn($q) => $q->where('owner_id', $user->id))
                    ->exists();
            }
        }

        return true;
    }

    /**
     * Determine whether the user can mark the order as delivered.
     */
    public function deliver(User $user, Order $order): bool
    {
        if (!$user->can('orders.deliver')) {
            return false;
        }

        // Farmer can only mark orders containing their products as delivered
        if ($user->hasRole('peternak')) {
            $shipmentStatus = $order->shipmentStatusRelation?->code;
            if ($shipmentStatus === 'shipped') {
                return $order->items()
                    ->whereHas('product', fn($q) => $q->where('owner_id', $user->id))
                    ->exists();
            }
        }

        return true;
    }

    /**
     * Determine whether the user can mark the order as completed.
     */
    public function complete(User $user, Order $order): bool
    {
        if (!$user->can('orders.complete')) {
            return false;
        }

        // Customer can only confirm delivery completion for their own orders
        if ($order->user_id === $user->id) {
            $shipmentStatus = $order->shipmentStatusRelation?->code;
            return $shipmentStatus === 'delivered';
        }

        // Admin/Staff can complete any order
        return true;
    }
}
