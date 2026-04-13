<?php

namespace App\Livewire\Home;

use Livewire\Component;

class Categories extends Component
{
    public array $categories = [];

    public function mount(array $categories): void
    {
        $this->categories = $categories;
    }

    public function render()
    {
        return view('livewire.home.categories');
    }
}
