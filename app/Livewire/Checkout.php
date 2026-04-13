<?php

namespace App\Livewire;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.guest-home')]
#[Title('Checkout FreshGo')]
class Checkout extends Component
{
    private const CART_SESSION_KEY = 'freshgo.cart';

    public string $name = '';

    public string $phone = '';

    public string $address = '';

    public string $notes = '';

    public bool $orderPlaced = false;

    public ?string $placedOrderNumber = null;

    public function placeOrder(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:300'],
            'notes' => ['nullable', 'string', 'max:300'],
        ]);

        if ($this->cartCount === 0) {
            $this->addError('cart', 'Keranjang kosong. Tambahkan produk terlebih dahulu.');

            return;
        }

        $items = $this->cartItems;

        $order = DB::transaction(function () use ($items): Order {
            $order = Order::create([
                'order_number' => 'FG-'.now()->format('YmdHis').'-'.str_pad((string) random_int(0, 999), 3, '0', STR_PAD_LEFT),
                'customer_name' => $this->name,
                'customer_phone' => $this->phone,
                'customer_address' => $this->address,
                'notes' => $this->notes,
                'total_items' => $this->cartCount,
                'total_amount' => $this->cartTotal,
                'status' => 'pending',
                'placed_at' => now(),
            ]);

            $order->items()->createMany(array_map(
                static fn (array $item): array => [
                    'product_name' => $item['name'],
                    'product_image' => $item['image'] ?? null,
                    'unit_price' => (int) $item['price'],
                    'quantity' => (int) $item['qty'],
                    'subtotal' => ((int) $item['price']) * ((int) $item['qty']),
                ],
                $items
            ));

            return $order;
        });

        session()->forget(self::CART_SESSION_KEY);
        $this->orderPlaced = true;
        $this->placedOrderNumber = $order->order_number;
    }

    #[Computed]
    public function cartItems(): array
    {
        return array_values(session(self::CART_SESSION_KEY, []));
    }

    #[Computed]
    public function cartCount(): int
    {
        return array_sum(array_column($this->cartItems, 'qty'));
    }

    #[Computed]
    public function cartTotal(): int
    {
        return array_sum(array_map(
            fn (array $item): int => ((int) $item['price']) * ((int) $item['qty']),
            $this->cartItems
        ));
    }

    public function render()
    {
        return view('livewire.checkout');
    }
}
