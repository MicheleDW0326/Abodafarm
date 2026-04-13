<?php

namespace App\Livewire\Home;

use Livewire\Component;

class Highlights extends Component
{
    public array $highlights = [];

    public function mount(array $highlights): void
    {
        $this->highlights = $highlights;
    }

    public function render()
    {
        return view('livewire.home.highlights');
    }
}
