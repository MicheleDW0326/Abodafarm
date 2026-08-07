<?php

namespace App\Livewire\Staff;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.main')]
#[Title('Profil Saya - Staff AbodaFarm')]
class Profile extends Component
{
    public function render()
    {
        return view('livewire.staff.profile');
    }
}
