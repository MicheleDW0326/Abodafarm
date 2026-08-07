<?php

namespace App\Livewire\Pelanggan;

use App\Models\Favorite;
use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.main')]
#[Title('Wishlist - Akun Saya')]
class Wishlist extends Component
{
    public array $wishlistItems = [];

    public function mount()
    {
        $this->loadWishlist();
    }

    public function loadWishlist(): void
    {
        $user = auth()->user();

        $favorites = Favorite::where('user_id', $user->id)
            ->where('favoritable_type', Product::class)
            ->with(['favoritable.category'])
            ->orderByDesc('created_at')
            ->get();

        $this->wishlistItems = $favorites->map(function ($favorite) {
            $product = $favorite->favoritable;
            if (!$product) {
                return null;
            }

            return [
                'id' => $favorite->id,
                'product_id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => $product->formatted_current_price,
                'original_price' => $product->formatted_price,
                'has_discount' => $product->has_discount,
                'image' => $product->image_url,
                'stock' => $product->stock,
                'stock_status' => $product->stock_status,
                'category' => $product->category?->name,
                'type' => $product->type,
                'in_stock' => $product->stock > 0,
                'added_at' => $favorite->created_at->diffForHumans(),
            ];
        })->filter()->values()->toArray();
    }

    public function removeFromWishlist(int $productId): void
    {
        Favorite::where('user_id', auth()->id())
            ->where('favoritable_type', Product::class)
            ->where('favoritable_id', $productId)
            ->delete();

        $this->loadWishlist();

        $this->dispatch('wishlist-updated');

        $this->dispatch('notify', [
            'type' => 'success',
            'title' => 'Berhasil',
            'message' => 'Produk dihapus dari wishlist.',
        ]);
    }

    public function moveToCart(int $productId): void
    {
        $product = Product::find($productId);

        if (!$product || $product->stock <= 0) {
            $this->dispatch('notify', [
                'type' => 'error',
                'title' => 'Gagal',
                'message' => 'Stok produk tidak tersedia.',
            ]);
            return;
        }

        // Get current cart from localStorage via JavaScript
        $this->dispatch('add-to-cart-from-wishlist', [
            'product_id' => $productId,
            'name' => $product->name,
            'price' => $product->current_price,
            'image' => $product->image_url,
            'type' => $product->type,
            'max_stock' => $product->stock,
        ]);

        // Remove from wishlist
        $this->removeFromWishlist($productId);

        $this->dispatch('notify', [
            'type' => 'success',
            'title' => 'Berhasil',
            'message' => 'Produk dipindahkan ke keranjang.',
        ]);
    }

    public function bookProduct(int $productId): void
    {
        $product = Product::find($productId);

        if (!$product) {
            $this->dispatch('notify', [
                'type' => 'error',
                'title' => 'Gagal',
                'message' => 'Produk tidak ditemukan.',
            ]);
            return;
        }

        // Remove from wishlist
        $this->removeFromWishlist($productId);

        // Redirect to product page for booking
        $this->redirectRoute('shop.product', $product->slug);
    }

    public function render()
    {
        return view('livewire.pelanggan.wishlist', [
            'items' => $this->wishlistItems,
        ]);
    }
}
