<?php

namespace App\Livewire\Home;

use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
class ProductCarousel extends Component
{
    public array $products = [];

    public string $title = '';

    public string $subtitle = '';

    public string $icon = 'fa-solid fa-fire';

    public string $iconColor = 'bg-amber-100 text-amber-600';

    public string $sectionId = '';

    public bool $showViewAll = true;

    public string $viewAllUrl = '/shop';

    public function mount(
        array $products = [],
        string $title = 'Produk',
        string $subtitle = '',
        string $icon = 'fa-solid fa-fire',
        string $iconColor = 'bg-amber-100 text-amber-600',
        string $sectionId = '',
        bool $showViewAll = true,
        string $viewAllUrl = '/shop'
    ): void {
        $this->products = $products;
        $this->title = $title;
        $this->subtitle = $subtitle;
        $this->icon = $icon;
        $this->iconColor = $iconColor;
        $this->sectionId = $sectionId;
        $this->showViewAll = $showViewAll;
        $this->viewAllUrl = $viewAllUrl;
    }

    public function render()
    {
        return view('livewire.home.product-carousel');
    }
}
