<?php

namespace App\Notifications;

use App\Models\Marketing\CustomerVoucher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewVoucherNotification extends Notification
{
    use Queueable;

    public function __construct(
        public CustomerVoucher $voucher
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('🎁 Anda Mendapat Voucher Baru!')
            ->greeting("Halo {$notifiable->name}!")
            ->line("Anda mendapatkan voucher baru: **{$this->voucher->name}**")
            ->line($this->voucher->description ?? '')
            ->line("**Diskon: {$this->voucher->formatted_discount}**")
            ->line("Minimum belanja: Rp " . number_format($this->voucher->minimum_order ?? 0, 0, ',', '.'))
            ->line("Berlaku hingga: " . $this->voucher->valid_until->format('d M Y'))
            ->action('Lihat Voucher', route('pelanggan.vouchers'))
            ->line('Selamat berbelanja!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'voucher_received',
            'voucher_id' => $this->voucher->id,
            'voucher_code' => $this->voucher->voucher_code,
            'name' => $this->voucher->name,
            'discount' => $this->voucher->formatted_discount,
            'valid_until' => $this->voucher->valid_until->toIso8601String(),
        ];
    }
}
