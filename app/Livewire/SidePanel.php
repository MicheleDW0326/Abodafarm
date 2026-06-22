<?php

namespace App\Livewire;

use Livewire\Component;

class SidePanel extends Component
{
    public bool $open = false;
    public string $message = '';

    public function toggle()
    {
        $this->open = ! $this->open;
    }

    public function sendMessage()
    {
        // placeholder: in future persist or forward message
        $this->message = '';
    }

    public function render()
    {
        return view('livewire.side-panel');
    }
}