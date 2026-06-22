<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Title;

class About extends Component
{
    #[Title('Tentang Kami - AbodaFarm')]
    public function render()
    {
        return view('livewire.home.about')->layout('layouts.guest-home');
    }
}
