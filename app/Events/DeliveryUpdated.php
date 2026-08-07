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

class DeliveryUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Order $order,
        public User $staff,
        public string $updateType,
        public array $updateData = []
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('order.' . $this->order->id),
        ];

        // Broadcast to customer
        if ($this->order->user_id) {
            $channels[] = new PrivateChannel('user.' . $this->order->user_id);
        }

        // Broadcast to assigned staff channel
        if ($this->order->assigned_staff_id) {
            $channels[] = new PrivateChannel('staff.' . $this->order->assigned_staff_id);
        }

        return $channels;
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'staff_id' => $this->staff->id,
            'staff_name' => $this->staff->name,
            'update_type' => $this->updateType,
            'update_data' => $this->updateData,
            'current_status' => $this->order->status,
            'updated_at' => now()->toIso8601String(),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'delivery.updated';
    }
}
