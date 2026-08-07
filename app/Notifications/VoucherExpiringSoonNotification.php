<?php

namespace App\Notifications;

use App\Models\Marketing\CustomerVoucher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VoucherExpiringSoonNotification extends Notification
{
    use Queueable;

    public function __construct(
        public CustomerVoucher $voucher,
        public int $daysRemaining
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('⚠️ Voucher Akan Segera Berakhir!')
            ->greeting("Halo {$notifiable->name}!")
            ->line("Voucher **{$this->voucher->name}** akan berakhir dalam **{$this->daysRemaining} hari**!")
            ->line("Jangan sampai kehilangan kesempatan ini.")
            ->action('Gunakan Sekarang', route('pelanggan.vouchers'))
            ->line('Selamat berbelanja!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'voucher_expiring',
            'voucher_id' => $this->voucher->id,
            'voucher_code' => $this->voucher->voucher_code,
            'name' => $this->voucher->name,
            'days_remaining' => $this->daysRemaining,
        ];
    }
}
