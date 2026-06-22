<?php

namespace App\Livewire\Home;

use Livewire\Component;

class Products extends Component
{
    /** @var array<int, array<string,mixed>> */
    public array $productsLive = [];

    /** @var array<int, array<string,mixed>> */
    public array $productsFresh = [];

    public function mount(array $productsLive = [], array $productsFresh = []): void
    {

        $this->productsLive = $productsLive;
        $this->productsFresh = $productsFresh;
    }

    public function addToCart(int $index): void
    {
        if (! isset($this->productsFresh[$index])) {
            return;
        }

        // Dispatch ke komponen parent (Home) agar cart tetap jalan seperti sebelumnya
        $this->dispatch('add-to-cart', product: $this->productsFresh[$index]);
    }

    public function showProductDetail(int $productId): void
    {
        $this->dispatch('show-product-detail', productId: $productId);
    }

    public function render()
    {
        return view('livewire.home.products');
    }

    public function placeholder(): \Illuminate\View\View
    {
        return view('livewire.home.products-skeleton');
    }
}