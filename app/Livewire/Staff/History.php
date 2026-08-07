<?php

namespace App\Livewire\Staff;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.main')]
#[Title('Riwayat - Staff AbodaFarm')]
class History extends Component
{
    public function render()
    {
        return view('livewire.staff.history');
    }
}
