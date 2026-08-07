<?php

namespace App\Livewire\Staff;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.main')]
#[Title('Pengiriman - Staff AbodaFarm')]
class Shipping extends Component
{
    public function render()
    {
        return view('livewire.staff.shipping');
    }
}
