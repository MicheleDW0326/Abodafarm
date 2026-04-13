<?php

namespace App\Livewire\Home;

use Livewire\Component;

class Products extends Component
{
    public array $products = [];

    public function mount(array $products): void
    {
        $this->products = $products;
    }

    public function addToCart(int $index): void
    {
        if (! isset($this->products[$index])) {
            return;
        }

        $this->dispatch('add-to-cart', product: $this->products[$index]);
    }

    public function render()
    {
        return view('livewire.home.products');
    }
}
