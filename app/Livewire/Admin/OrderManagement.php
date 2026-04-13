<?php

namespace App\Livewire\Admin;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Menejemen Pemesanan')]
class OrderManagement extends Component
{
    public function render()
    {
        return view('livewire.admin.order-management');
    }
}
