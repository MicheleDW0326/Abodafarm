<?php

namespace App\Livewire;

use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.guest-home')]
#[Title('FreshGo')]
class Home extends Component
{
    private const CART_SESSION_KEY = 'freshgo.cart';

    public array $heroSlides = [];

    public array $categories = [];

    public array $products = [];

    public array $highlights = [];

    public array $deliverySteps = [];

    public array $testimonials = [];

    public array $faqs = [];

    public array $cart = [];

    public function mount(): void
    {
        $this->cart = session(self::CART_SESSION_KEY, []);

        $this->heroSlides = [
            [
                'title' => 'FreshGo: Unggas dan Seafood Premium',
                'description' => 'Pilihan ayam segar dan seafood tangkapan harian. Higienis, cepat, dan siap dikirim ke rumahmu.',
                'image' => 'https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?auto=format&fit=crop&w=1600&q=80',
            ],
            [
                'title' => 'Seafood Segar Tangkapan Harian',
                'description' => 'Ikan laut berkualitas yang diproses bersih dan dikirim dengan rantai dingin.',
                'image' => 'https://images.unsplash.com/photo-1611171711917-4d7f71eb6cf7?auto=format&fit=crop&w=1600&q=80',
            ],
            [
                'title' => 'Unggas Segar Langsung dari Mitra Peternak',
                'description' => 'Daging ayam berkualitas untuk kebutuhan rumah tangga hingga bisnis kuliner.',
                'image' => 'https://images.unsplash.com/photo-1604503468506-a8da13d82791?auto=format&fit=crop&w=1600&q=80',
            ],
        ];

        $this->categories = [
            [
                'name' => 'Unggas Segar',
                'description' => 'Ayam utuh, fillet, paha, sayap, dan olahan pilihan.',
                'image' => 'https://images.unsplash.com/photo-1587593810167-a84920ea0781?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'name' => 'Seafood Harian',
                'description' => 'Ikan, udang, cumi, dan seafood segar harian.',
                'image' => 'https://images.unsplash.com/photo-1615141982883-c7ad0e69fd62?auto=format&fit=crop&w=800&q=80',
            ],
        ];

        $this->products = [
            ['name' => 'Ayam Fillet Premium', 'price' => 48000, 'category' => 'poultry', 'image' => 'https://images.unsplash.com/photo-1604503468506-a8da13d82791?auto=format&fit=crop&w=600&q=80'],
            ['name' => 'Paha Ayam Fresh', 'price' => 39000, 'category' => 'poultry', 'image' => 'https://images.unsplash.com/photo-1608039755401-742074f0548d?auto=format&fit=crop&w=600&q=80'],
            ['name' => 'Sayap Ayam', 'price' => 35000, 'category' => 'poultry', 'image' => 'https://images.unsplash.com/photo-1562967914-01efa7b011a3?auto=format&fit=crop&w=600&q=80'],
            ['name' => 'Ayam Utuh 1kg', 'price' => 52000, 'category' => 'poultry', 'image' => 'https://images.unsplash.com/photo-1604503468506-a8da13d82791?auto=format&fit=crop&w=600&q=80'],
            ['name' => 'Salmon Fillet', 'price' => 118000, 'category' => 'seafood', 'image' => 'https://images.unsplash.com/photo-1599084993091-1cb5c0721cc6?auto=format&fit=crop&w=600&q=80'],
            ['name' => 'Udang Vaname', 'price' => 86000, 'category' => 'seafood', 'image' => 'https://images.unsplash.com/photo-1625943553852-781c6dd46faa?auto=format&fit=crop&w=600&q=80'],
            ['name' => 'Cumi Bersih', 'price' => 73000, 'category' => 'seafood', 'image' => 'https://images.unsplash.com/photo-1615141982883-c7ad0e69fd62?auto=format&fit=crop&w=600&q=80'],
            ['name' => 'Ikan Kakap Merah', 'price' => 79000, 'category' => 'seafood', 'image' => 'https://images.unsplash.com/photo-1574781330855-d0db8cc6a79c?auto=format&fit=crop&w=600&q=80'],
        ];

        $this->highlights = [
            ['title' => '100% Halal', 'description' => 'Proses pemotongan dan penanganan sesuai standar halal.'],
            ['title' => 'Daily Delivery', 'description' => 'Pengiriman harian cepat dengan armada berpendingin.'],
            ['title' => 'Dari Sumber ke Meja Makan', 'description' => 'Rantai pasok pendek untuk menjaga kesegaran maksimal.'],
            ['title' => 'Lolos Quality Check', 'description' => 'Setiap batch diperiksa sebelum siap kirim.'],
        ];

        $this->deliverySteps = [
            ['title' => 'Pilih Produk', 'description' => 'Pilih ayam dan seafood favoritmu dari katalog FreshGo.'],
            ['title' => 'Kami Siapkan', 'description' => 'Produk dipacking higienis sesuai standar food-grade.'],
            ['title' => 'Kirim Dingin', 'description' => 'Pesanan dikirim dengan cold-chain untuk menjaga kualitas.'],
            ['title' => 'Masak & Nikmati', 'description' => 'Terima produk segar, siap olah untuk keluarga.'],
        ];

        $this->testimonials = [
            ['name' => 'Nadia', 'role' => 'Ibu Rumah Tangga', 'quote' => 'Ayamnya bersih, tidak amis, dan selalu fresh.'],
            ['name' => 'Rian', 'role' => 'Owner Resto', 'quote' => 'Kualitas seafood konsisten, cocok untuk bisnis kuliner.'],
            ['name' => 'Salsa', 'role' => 'Pecinta Masak', 'quote' => 'Belanja cepat, kirim rapi, harga juga masuk akal.'],
        ];

        $this->faqs = [
            ['question' => 'Apakah FreshGo melayani pengiriman di hari yang sama?', 'answer' => 'Ya. Untuk area tertentu, pesanan sebelum pukul 14:00 bisa dikirim di hari yang sama.'],
            ['question' => 'Bagaimana menjaga produk tetap segar saat pengiriman?', 'answer' => 'Kami menggunakan kemasan food-grade dan cold-chain delivery.'],
            ['question' => 'Apakah bisa request potongan khusus?', 'answer' => 'Bisa. Tinggal tulis catatan saat checkout untuk request potongan.'],
            ['question' => 'Metode pembayaran apa saja yang tersedia?', 'answer' => 'Transfer bank, e-wallet, dan pembayaran melalui gateway online.'],
        ];
    }

    #[On('add-to-cart')]
    public function addToCart(array $product): void
    {
        $key = md5($product['name']);

        if (isset($this->cart[$key])) {
            $this->cart[$key]['qty']++;

            $this->persistCart();
            $this->dispatch('cart-item-added', name: $product['name']);

            return;
        }

        $this->cart[$key] = [
            'key' => $key,
            'name' => $product['name'],
            'price' => (int) $product['price'],
            'image' => $product['image'],
            'qty' => 1,
        ];

        $this->persistCart();
        $this->dispatch('cart-item-added', name: $product['name']);
    }

    public function increaseQty(string $key): void
    {
        if (! isset($this->cart[$key])) {
            return;
        }

        $this->cart[$key]['qty']++;
        $this->persistCart();
    }

    public function decreaseQty(string $key): void
    {
        if (! isset($this->cart[$key])) {
            return;
        }

        if ($this->cart[$key]['qty'] <= 1) {
            unset($this->cart[$key]);

            $this->persistCart();

            return;
        }

        $this->cart[$key]['qty']--;
        $this->persistCart();
    }

    public function removeItem(string $key): void
    {
        unset($this->cart[$key]);
        $this->persistCart();
    }

    public function clearCart(): void
    {
        $this->cart = [];
        $this->persistCart();
    }

    private function persistCart(): void
    {
        session([self::CART_SESSION_KEY => $this->cart]);
    }

    #[Computed]
    public function cartItems(): array
    {
        return array_values($this->cart);
    }

    #[Computed]
    public function cartCount(): int
    {
        return array_sum(array_column($this->cart, 'qty'));
    }

    #[Computed]
    public function cartTotal(): int
    {
        return array_sum(array_map(
            fn (array $item): int => $item['price'] * $item['qty'],
            $this->cart
        ));
    }

    public function render()
    {
        return view('livewire.home');
    }
}
