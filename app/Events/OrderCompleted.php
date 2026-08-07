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

class OrderCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Order $order,
        public ?User $completedBy,
        public bool $isAutomatic = false,
        public ?string $completedAt = null
    ) {}

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('order.' . $this->order->id),
            new PrivateChannel('user.' . $this->order->user_id),
            new PrivateChannel('admin'),
            new PrivateChannel('staff.' . $this->order->assigned_staff_id),
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
            'is_automatic' => $this->isAutomatic,
            'completed_at' => $this->completedAt ?? $this->order->completed_at?->toIso8601String(),
            'completed_by' => $this->completedBy ? [
                'id' => $this->completedBy->id,
                'name' => $this->completedBy->name,
            ] : null,
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'order.completed';
    }
}
