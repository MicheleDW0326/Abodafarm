<?php

namespace App\Events;

use App\Models\Marketing\CustomerVoucher;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VoucherClaimed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public CustomerVoucher $voucher,
        public User $user
    ) {}
}
