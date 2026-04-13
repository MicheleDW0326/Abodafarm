<?php

namespace App\Livewire\Admin;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Pengaturan Toko')]
class StoreSetting extends Component
{
    public function render()
    {
        return view('livewire.admin.store-setting');
    }
}
