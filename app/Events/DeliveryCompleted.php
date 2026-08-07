<?php

namespace App\Events;

use App\Models\Order;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeliveryCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Order $order,
        public User $staff,
        public ?string $receiverName,
        public ?string $deliveryPhoto,
        public ?string $deliveryNote,
        public ?float $latitude,
        public ?float $longitude,
        public ?string $deliveryTime
    ) {}

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('order.' . $this->order->id),
            new PrivateChannel('user.' . $this->order->user_id),
            new PrivateChannel('staff.' . $this->staff->id),
            new PrivateChannel('admin'),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'status' => $this->order->status,
            'receiver_name' => $this->receiverName,
            'delivery_photo' => $this->deliveryPhoto ? asset('storage/' . $this->deliveryPhoto) : null,
            'delivery_note' => $this->deliveryNote,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'delivery_time' => $this->deliveryTime,
            'staff' => [
                'id' => $this->staff->id,
                'name' => $this->staff->name,
            ],
            'completed_at' => $this->order->delivery_completed_at?->toIso8601String(),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'delivery.completed';
    }
}
