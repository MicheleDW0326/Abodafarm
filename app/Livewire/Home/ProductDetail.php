<?php

namespace App\Livewire\Home;

use App\Models\Product;
use Livewire\Component;

class ProductDetail extends Component
{
    public function render()
    {
        return view('livewire.home.product-detail');
    }
}
