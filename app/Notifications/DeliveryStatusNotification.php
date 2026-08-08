<?php

namespace App\Notifications;

use App\Models\Delivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeliveryStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Delivery $delivery,
        protected string $status,
        protected string $message
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Update Pengiriman - ' . $this->delivery->delivery_code)
            ->line($this->message)
            ->line('Kode Pengiriman: ' . $this->delivery->delivery_code)
            ->line('Status: ' . $this->status)
            ->action('Lihat Detail', url('/pelanggan/pesanan/' . $this->delivery->order_id))
            ->line('Terima kasih telah berbelanja di AbodaFarm!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'delivery_status',
            'delivery_id' => $this->delivery->id,
            'order_id' => $this->delivery->order_id,
            'delivery_code' => $this->delivery->delivery_code,
            'status' => $this->delivery->status,
            'status_label' => $this->status,
            'message' => $this->message,
            'courier_name' => $this->delivery->courier_name,
            'tracking_number' => $this->delivery->tracking_number,
        ];
    }
}
