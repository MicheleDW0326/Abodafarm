<?php

namespace App\Livewire\Admin;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Menejemen Pelanggan')]
class CustomerManagement extends Component
{
    public function render()
    {
        return view('livewire.admin.customer-management');
    }
}
