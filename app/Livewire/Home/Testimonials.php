<?php

namespace App\Livewire\Home;

use Livewire\Component;

class Testimonials extends Component
{
    public array $testimonials = [];

    public function mount(array $testimonials): void
    {
        $this->testimonials = $testimonials;
    }

    public function render()
    {
        return view('livewire.home.testimonials');
    }
}
