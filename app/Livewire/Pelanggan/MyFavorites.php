<?php

namespace App\Livewire\Pelanggan;

use App\Models\Favorite;
use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.main')]
#[Title('Favorit - Akun Saya')]
class MyFavorites extends Component
{
    public array $favorites = [];
    public string $typeFilter = 'all';

    public array $typeOptions = [
        'all' => 'Semua',
        'live' => 'Ternak',
        'fresh' => 'Daging Segar',
    ];

    public function mount(): void
    {
        $this->loadFavorites();
    }

    public function render()
    {
        return view('livewire.pelanggan.my-favorites');
    }

    public function loadFavorites(): void
    {
        $userId = auth()->id();

        $query = Favorite::forUser($userId)
            ->where('favoritable_type', Product::class)
            ->with(['favoritable']);

        if ($this->typeFilter !== 'all') {
            $query->whereHas('favoritable', function ($q) {
                $q->where('type', $this->typeFilter);
            });
        }

        $this->favorites = $query->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($favorite) {
                return [
                    'id' => $favorite->id,
                    'product' => $favorite->favoritable,
                    'created_at' => $favorite->created_at,
                ];
            })
            ->toArray();
    }

    public function removeFavorite(int $productId): void
    {
        Favorite::toggle(auth()->id(), Product::class, $productId);
        $this->loadFavorites();

        $this->dispatch('notify', [
            'type' => 'success',
            'title' => 'Berhasil',
            'message' => 'Produk dihapus dari favorit.',
        ]);
    }

    public function updatedTypeFilter(): void
    {
        $this->loadFavorites();
    }
}
