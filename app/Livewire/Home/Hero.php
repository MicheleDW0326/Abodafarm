<?php

namespace App\Livewire\Home;

use Livewire\Component;

class Hero extends Component
{
    public array $heroSlides = [];

    public function mount(array $heroSlides): void
    {
        $this->heroSlides = $heroSlides;
    }

    public function render()
    {
        return view('livewire.home.hero');
    }
}
