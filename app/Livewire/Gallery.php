<?php

namespace App\Livewire;

use Livewire\Component;

class Gallery extends Component
{
    public function mount(): void
    {
        // Redirect to shop since gallery is now consolidated into the unified Produk page
        $this->redirect(route('shop'), navigate: true);
    }

    public function render()
    {
        return view('livewire.gallery');
    }
}
