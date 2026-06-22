<?php

namespace App\Livewire\Home;

use Livewire\Component;

class Delivery extends Component
{
    public array $deliverySteps = [];

    public function mount(array $deliverySteps): void
    {
        $this->deliverySteps = $deliverySteps;
    }

    public function render()
    {
        return view('livewire.home.delivery');
    }

    public function placeholder(): \Illuminate\View\View
    {
        return view('livewire.home.delivery-skeleton');
    }
}
