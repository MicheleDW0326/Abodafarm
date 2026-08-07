<?php

namespace App\Livewire\Staff;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.main')]
#[Title('Notifikasi - Staff AbodaFarm')]
class Notifications extends Component
{
    public function render()
    {
        return view('livewire.staff.notifications');
    }
}
