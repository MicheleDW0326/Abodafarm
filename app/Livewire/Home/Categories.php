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

    public function selectCategory(string $term): void
    {
        $this->dispatch('category-filter', term: $term);
    }

    public function render()
    {
        return view('livewire.home.categories');
    }

    public function placeholder(): \Illuminate\View\View
    {
        return view('livewire.home.categories-skeleton');
    }
}
