<?php

namespace App\Livewire\Home;

use Livewire\Component;

class Faq extends Component
{
    public array $faqs = [];

    public function mount(array $faqs): void
    {
        $this->faqs = $faqs;
    }

    public function render()
    {
        return view('livewire.home.faq');
    }

    public function placeholder(): \Illuminate\View\View
    {
        return view('livewire.home.faq-skeleton');
    }
}
