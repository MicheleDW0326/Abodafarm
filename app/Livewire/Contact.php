<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Title;

class Contact extends Component
{
    #[Title('Hubungi Kami - AbodaFarm')]
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $subject = '';
    public string $message = '';
    public bool $submitted = false;
    public bool $useWhatsApp = false;

    protected $rules = [
        'name' => 'required|min:3',
        'email' => 'required|email',
        'phone' => 'nullable|string|max:20',
        'subject' => 'required|min:5',
        'message' => 'required|min:10',
    ];

    protected $messages = [
        'name.required' => 'Nama harus diisi',
        'email.required' => 'Email harus diisi',
        'email.email' => 'Format email tidak valid',
        'subject.required' => 'Subjek harus diisi',
        'message.required' => 'Pesan harus diisi',
        'message.min' => 'Pesan minimal 10 karakter',
    ];

    public function updated($property): void
    {
        if (in_array($property, ['name', 'email', 'phone', 'subject', 'message'])) {
            $this->submitted = false;
        }
    }

    public function sendViaMail(): void
    {
        $this->validate();

        $this->submitted = true;
    }

    public function getMailtoUrlProperty(): string
    {
        $to = 'darindingilan@gmail.com';
        $subject = rawurlencode($this->subject ?: 'Pertanyaan dari Website AbodaFarm');
        $body = rawurlencode("Nama: {$this->name}\n");
        $body .= rawurlencode("Email: {$this->email}\n");
        if ($this->phone) {
            $body .= rawurlencode("Telepon: {$this->phone}\n");
        }
        $body .= rawurlencode("\n---\n");
        $body .= rawurlencode($this->message);

        return "mailto:{$to}?subject={$subject}&body={$body}";
    }

    public function getWhatsAppUrlProperty(): string
    {
        $phone = '6282216224715';
        $text = rawurlencode("Halo AbodaFarm!\n\n");
        $text .= rawurlencode("Nama: {$this->name}\n");
        $text .= rawurlencode("Email: {$this->email}\n");
        if ($this->phone) {
            $text .= rawurlencode("Telepon: {$this->phone}\n");
        }
        $text .= rawurlencode("\n---\n");
        $text .= rawurlencode($this->message);

        return "https://wa.me/{$phone}?text={$text}";
    }

    public function resetForm(): void
    {
        $this->reset(['name', 'email', 'phone', 'subject', 'message', 'submitted']);
    }

    public function render()
    {
        return view('livewire.home.contact')->layout('layouts.guest-home');
    }
}
