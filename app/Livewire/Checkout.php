<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\Payment;
use App\Services\MidtransService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.guest-home')]
#[Title('Checkout AbodaFarm')]
class Checkout extends Component
{
    private const CART_SESSION_KEY = 'abodafarm.cart';

    public string $name = '';

    public string $phone = '';

    public string $address = '';

    public string $notes = '';

    public ?string $cartData = null;

    public bool $orderPlaced = false;

    public ?string $placedOrderNumber = null;

    // Payment related properties
    public bool $showPaymentModal = false;

    public bool $isLoadingPayment = false;

    public ?string $snapToken = null;

    public ?int $currentOrderId = null;

    public ?string $errorMessage = null;

    public function placeOrder(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:300'],
            'notes' => ['nullable', 'string', 'max:300'],
        ]);

        // Get cart items - prioritize cartData from hidden input
        $items = $this->getCartItems();

        if (empty($items)) {
            $this->addError('cart', 'Keranjang kosong. Tambahkan produk terlebih dahulu.');
            return;
        }

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

        // Store order ID for payment processing
        $this->currentOrderId = $order->id;
        $this->placedOrderNumber = $order->order_number;
        $this->orderPlaced = true;

        // Clear cart
        session()->forget(self::CART_SESSION_KEY);

        // Automatically open payment modal
        $this->dispatch('refresh-cart');
        $this->openPaymentModal();
    }

    /**
     * Open payment modal and generate Snap token.
     */
    public function openPaymentModal(): void
    {
        if (!$this->currentOrderId) {
            $this->errorMessage = 'Order tidak ditemukan.';
            return;
        }

        $this->isLoadingPayment = true;
        $this->errorMessage = null;
        $this->snapToken = null;

        try {
            $order = Order::with('items.product')->find($this->currentOrderId);

            if (!$order) {
                $this->errorMessage = 'Order tidak ditemukan.';
                $this->isLoadingPayment = false;
                return;
            }

            // Check if payment already exists
            $existingPayment = $order->payment;

            if ($existingPayment && $existingPayment->isCompleted()) {
                $this->errorMessage = 'Pembayaran sudah selesai.';
                $this->isLoadingPayment = false;
                return;
            }

            // Generate Snap token
            $result = MidtransService::createSnapToken($order);

            $this->snapToken = $result['snap_token'];
            $this->showPaymentModal = true;

            // Debug log
            \Log::info('Snap token created in Checkout', [
                'snap_token' => $this->snapToken,
                'order_id' => $this->currentOrderId,
            ]);

        } catch (\Exception $e) {
            $this->errorMessage = 'Gagal membuat token pembayaran: ' . $e->getMessage();
            \Log::error('Midtrans Snap Token Error', [
                'order_id' => $this->currentOrderId,
                'error' => $e->getMessage(),
            ]);
        } finally {
            $this->isLoadingPayment = false;
        }
    }

    /**
     * Close payment modal.
     */
    public function closePaymentModal(): void
    {
        $this->showPaymentModal = false;
        $this->snapToken = null;
    }

    /**
     * Refresh payment status from server.
     */
    public function refreshPaymentStatus(): void
    {
        if (!$this->currentOrderId) {
            return;
        }

        try {
            $order = Order::with('payment')->find($this->currentOrderId);

            if ($order && $order->payment) {
                // Refresh status from Midtrans
                $midtransStatus = MidtransService::checkTransactionStatus($order->order_number);

                if ($midtransStatus) {
                    // Update payment record
                    $order->payment->update([
                        'transaction_id' => $midtransStatus->transaction_id ?? $order->payment->transaction_id,
                        'payment_type' => $midtransStatus->payment_type ?? $order->payment->payment_type,
                        'transaction_status' => $midtransStatus->transaction_status ?? $order->payment->transaction_status,
                        'fraud_status' => $midtransStatus->fraud_status ?? $order->payment->fraud_status,
                    ]);

                    // If payment is completed
                    if (in_array($midtransStatus->transaction_status, ['capture', 'settlement'])) {
                        $order->payment->update(['paid_at' => now()]);
                        $order->update(['status' => 'paid']);
                        $order->refresh();
                        $order->payment->refresh();
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error('Refresh Payment Status Error', [
                'order_id' => $this->currentOrderId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Check if payment is completed.
     */
    #[Computed]
    public function isPaymentCompleted(): bool
    {
        if (!$this->currentOrderId) {
            return false;
        }

        $order = Order::with('payment')->find($this->currentOrderId);

        return $order && $order->payment && $order->payment->isCompleted();
    }

    /**
     * Get payment status for display.
     */
    #[Computed]
    public function paymentStatus(): ?array
    {
        if (!$this->currentOrderId) {
            return null;
        }

        $order = Order::with('payment')->find($this->currentOrderId);

        if (!$order || !$order->payment) {
            return null;
        }

        return [
            'status' => $order->payment->transaction_status,
            'payment_type' => $order->payment->payment_type,
            'is_completed' => $order->payment->isCompleted(),
            'is_pending' => $order->payment->isPending(),
            'is_failed' => $order->payment->isFailed(),
            'formatted_amount' => $order->payment->formatted_amount,
            'paid_at' => $order->payment->paid_at?->format('d/m/Y H:i'),
            'transaction_id' => $order->payment->transaction_id,
        ];
    }

    #[Computed]
    public function cartItems(): array
    {
        return $this->getCartItems();
    }

    /**
     * Get cart items from various sources.
     */
    public function getCartItems(): array
    {
        // Priority 1: cartData from hidden input (submitted form)
        if (!empty($this->cartData)) {
            $decoded = json_decode($this->cartData, true);
            if (is_array($decoded) && !empty($decoded)) {
                return $decoded;
            }
        }

        // Priority 2: session cart
        $sessionCart = session(self::CART_SESSION_KEY, []);
        if (!empty($sessionCart)) {
            return array_values($sessionCart);
        }

        return [];
    }

    #[Computed]
    public function cartCount(): int
    {
        $items = $this->getCartItems();
        return array_sum(array_column($items, 'qty'));
    }

    #[Computed]
    public function cartTotal(): int
    {
        $items = $this->getCartItems();
        return array_sum(array_map(
            fn (array $item): int => ((int) $item['price']) * ((int) $item['qty']),
            $items
        ));
    }

    /**
     * Get Midtrans client key for frontend.
     */
    public function getMidtransClientKey(): string
    {
        return config('services.midtrans.client_key', '');
    }

    /**
     * Refresh cart from session (called from frontend after localStorage sync).
     */
    public function refreshCart(): void
    {
        // Force Livewire to re-render by toggling a dummy property
        $this->dispatch('cart-refreshed');
    }

    public function render()
    {
        return view('livewire.checkout');
    }
}
