<?php

namespace App\Events;

use App\Models\Marketing\CustomerVoucher;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VoucherExpired
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public CustomerVoucher $voucher
    ) {}
}
