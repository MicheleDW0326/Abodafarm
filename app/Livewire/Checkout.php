<?php

namespace App\Livewire;

use App\Enums\PaymentStatusEnum;
use App\Models\Address;
use App\Models\CustomerNotification;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Models\Payment;
use App\Services\FcmService;
use App\Services\MidtransService;
use App\Services\OrderStateMachine;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.guest-home')]
#[Title('Checkout AbodaFarm')]
class Checkout extends Component
{
    private const CART_SESSION_KEY = 'abodafarm.cart';
    private const CHECKOUT_FORM_KEY = 'abodafarm_checkout_form';
    private const ORDER_ID_KEY = 'abodafarm_checkout_order_id';

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

    // Address properties
    public array $userAddresses = [];

    public ?int $selectedAddressId = null;

    public bool $useNewAddress = false;

    public string $newAddressLabel = '';

    public string $newFullAddress = '';

    public string $newProvince = '';

    public string $newCity = '';

    public string $newDistrict = '';

    public string $newPostalCode = '';

    // Service area validation
    public bool $isInServiceArea = true;
    public ?string $serviceAreaMessage = null;

    // Show address modal (for inline address creation)
    public bool $showAddressModal = false;

    public function mount(): void
    {
        // Restore form state from session if exists
        $this->restoreFormState();

        // Restore order ID if exists (for mid-order reload)
        $savedOrderId = Session::get(self::ORDER_ID_KEY);
        if ($savedOrderId) {
            $this->currentOrderId = $savedOrderId;
            $order = Order::find($savedOrderId);
            if ($order) {
                $this->placedOrderNumber = $order->order_number;
                $this->orderPlaced = true;
            }
        }

        $this->loadUserAddresses();
    }

    /**
     * Restore form state from session.
     */
    protected function restoreFormState(): void
    {
        $saved = Session::get(self::CHECKOUT_FORM_KEY);

        if ($saved && is_array($saved)) {
            $this->name = $saved['name'] ?? '';
            $this->phone = $saved['phone'] ?? '';
            $this->address = $saved['address'] ?? '';
            $this->notes = $saved['notes'] ?? '';
            $this->selectedAddressId = $saved['selectedAddressId'] ?? null;
            $this->useNewAddress = $saved['useNewAddress'] ?? false;
            $this->newAddressLabel = $saved['newAddressLabel'] ?? '';
            $this->newFullAddress = $saved['newFullAddress'] ?? '';
            $this->newProvince = $saved['newProvince'] ?? '';
            $this->newCity = $saved['newCity'] ?? '';
            $this->newDistrict = $saved['newDistrict'] ?? '';
            $this->newPostalCode = $saved['newPostalCode'] ?? '';
        }
    }

    /**
     * Save form state to session.
     */
    protected function saveFormState(): void
    {
        Session::put(self::CHECKOUT_FORM_KEY, [
            'name' => $this->name,
            'phone' => $this->phone,
            'address' => $this->address,
            'notes' => $this->notes,
            'selectedAddressId' => $this->selectedAddressId,
            'useNewAddress' => $this->useNewAddress,
            'newAddressLabel' => $this->newAddressLabel,
            'newFullAddress' => $this->newFullAddress,
            'newProvince' => $this->newProvince,
            'newCity' => $this->newCity,
            'newDistrict' => $this->newDistrict,
            'newPostalCode' => $this->newPostalCode,
        ]);
    }

    /**
     * Clear saved form state.
     */
    protected function clearFormState(): void
    {
        Session::forget(self::CHECKOUT_FORM_KEY);
        Session::forget(self::ORDER_ID_KEY);
    }

    public function loadUserAddresses(): void
    {
        if (!auth()->check()) {
            $this->userAddresses = [];
            return;
        }

        $this->userAddresses = Address::where('user_id', auth()->id())
            ->orderByDesc('is_default')
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();

        // Only auto-fill if there's no saved form state
        $hasSavedState = Session::has(self::CHECKOUT_FORM_KEY);
        $hasExistingOrder = Session::has(self::ORDER_ID_KEY);

        // If user already has saved state or existing order, don't auto-fill
        if ($hasSavedState || $hasExistingOrder) {
            return;
        }

        // Auto-fill name and phone from user profile
        $this->name = auth()->user()->name ?? '';
        $this->phone = auth()->user()->phone ?? '';

        // Auto-select default address
        $defaultAddress = collect($this->userAddresses)->firstWhere('is_default', true);
        if ($defaultAddress) {
            $this->selectedAddressId = $defaultAddress['id'];
            $this->address = $defaultAddress['address'];
            $this->phone = $defaultAddress['phone'] ?: $this->phone;
        } elseif (!empty($this->userAddresses)) {
            // If no default, select first address
            $firstAddress = $this->userAddresses[0] ?? null;
            if ($firstAddress) {
                $this->selectedAddressId = $firstAddress['id'];
                $this->address = $firstAddress['address'];
                $this->phone = $firstAddress['phone'] ?: $this->phone;
            }
        }
    }

    public function selectAddress(int $addressId): void
    {
        $this->selectedAddressId = $addressId;
        $this->useNewAddress = false;

        $address = collect($this->userAddresses)->firstWhere('id', $addressId);
        if ($address) {
            $this->address = $address['formatted_address'] ?? $address['address'];
            if ($address['phone']) {
                $this->phone = $address['phone'];
            }
            if ($address['latitude'] && $address['longitude']) {
                $this->checkServiceAreaFromAddress($address);
            }
        }

        $this->saveFormState();
    }

    public function showNewAddressForm(): void
    {
        $this->selectedAddressId = null;
        $this->useNewAddress = true;
        $this->address = '';
        $this->isInServiceArea = true;
        $this->serviceAreaMessage = null;

        $this->saveFormState();
    }

    public function checkServiceAreaFromAddress(array $address): void
    {
        // Simple check - in real implementation, use DeliveryArea model
        $city = strtolower($address['city'] ?? '');
        $province = strtolower($address['province'] ?? '');

        // Default service areas in Sulawesi Utara
        $serviceableCities = ['manado', 'bitung', 'tomohon', 'kotamobagu', 'minahasa', 'minahasa utara', 'minahasa selatan', 'minahasa tenggara', 'bolaang mongondow', 'sangihe', 'sitaro', 'talaud', 'donggala', 'parigi', 'moutong'];

        foreach ($serviceableCities as $cityName) {
            if (stripos($city, $cityName) !== false || stripos($province, 'sulawesi utara') !== false) {
                $this->isInServiceArea = true;
                $this->serviceAreaMessage = null;
                return;
            }
        }

        $this->isInServiceArea = false;
        $this->serviceAreaMessage = 'Mohon maaf, lokasi Anda mungkin di luar area layanan pengiriman.';
    }

    public function saveAddress(): void
    {
        if (!auth()->check()) {
            return;
        }

        $this->validate([
            'newAddressLabel' => 'required|string|max:50',
            'newFullAddress' => 'required|string|max:500',
            'newProvince' => 'required|string|max:100',
            'newCity' => 'required|string|max:100',
            'newDistrict' => 'required|string|max:100',
            'newPostalCode' => 'nullable|string|max:10',
        ]);

        $address = Address::create([
            'user_id' => auth()->id(),
            'label' => $this->newAddressLabel,
            'recipient_name' => $this->name,
            'phone' => $this->phone,
            'address' => $this->newFullAddress,
            'province' => $this->newProvince,
            'city' => $this->newCity,
            'district' => $this->newDistrict,
            'postal_code' => $this->newPostalCode,
            'is_default' => empty($this->userAddresses),
        ]);

        // Update current form data
        $this->address = $address->full_address;
        $this->selectedAddressId = $address->id;
        $this->useNewAddress = false;

        // Reload addresses
        $this->loadUserAddresses();

        // Save state to session
        $this->saveFormState();
    }

    public function useExistingAddress(): void
    {
        $this->useNewAddress = false;
        $this->newAddressLabel = '';
        $this->newFullAddress = '';
        $this->newProvince = '';
        $this->newCity = '';
        $this->newDistrict = '';
        $this->newPostalCode = '';

        // Restore selected address
        if ($this->selectedAddressId) {
            $this->selectAddress($this->selectedAddressId);
        }

        $this->saveFormState();
    }

    public function placeOrder(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:300'],
        ]);

        // Check if address is in service area (warning only, don't block)
        if (!$this->isInServiceArea && $this->serviceAreaMessage) {
            // Allow but warn - the address will still be saved
            // In production, you might want to block this
        }

        // Get cart items - prioritize cartData from hidden input
        $items = $this->getCartItems();

        if (empty($items)) {
            $this->addError('cart', 'Keranjang kosong. Tambahkan produk terlebih dahulu.');
            return;
        }

        // Validate stock for all cart items
        $stockErrors = $this->validateCartStock($items);
        if (!empty($stockErrors)) {
            $this->addError('cart', implode(', ', $stockErrors));
            return;
        }

        // Get courier notes if using saved address
        $courierNotes = null;
        if ($this->selectedAddressId) {
            $address = Address::find($this->selectedAddressId);
            if ($address) {
                $courierNotes = $address->courier_notes;
            }
        }

        $order = DB::transaction(function () use ($items): Order {
            $order = Order::create([
                'order_number' => 'FG-' . now()->format('YmdHis') . '-' . str_pad((string) random_int(0, 999), 3, '0', STR_PAD_LEFT),
                'user_id' => auth()->id(),
                'customer_name' => $this->name,
                'customer_phone' => $this->phone,
                'customer_address' => $this->address,
                'notes' => $this->notes,
                'total_items' => $this->cartCount,
                'total_amount' => $this->cartTotal,
                'status' => 'pending',
                'placed_at' => now(),
                'order_type' => Order::TYPE_CART,
            ]);

            $order->items()->createMany(array_map(
                static fn(array $item): array => [
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

        // Log initial status
        OrderStatusLog::logCreation(
            $order,
            OrderStatusLog::TRIGGER_CUSTOMER,
            auth()->id(),
            'Pesanan dibuat melalui checkout'
        );

        // Store order ID for payment processing
        $this->currentOrderId = $order->id;
        $this->placedOrderNumber = $order->order_number;
        $this->orderPlaced = true;

        // Save order ID to session (for reload persistence)
        Session::put(self::ORDER_ID_KEY, $order->id);

        // Save form state to session
        $this->saveFormState();

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

        // Check if order still exists
        $order = Order::find($this->currentOrderId);
        if (!$order) {
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

                        // Use new FK-based state machine for payment status
                        OrderStateMachine::setPaymentStatus(
                            $order,
                            PaymentStatusEnum::PAID,
                            OrderStatusLog::TRIGGER_WEBHOOK,
                            auth()->id(),
                            'Pembayaran berhasil via Midtrans'
                        );

                        // Decrement stock for all items
                        $this->decrementOrderStock($order);

                        // Send payment success notification
                        $this->sendPaymentSuccessNotification($order);

                        $order->refresh();
                        $order->payment->refresh();
                    }

                    // Handle expired/cancelled transactions
                    if (in_array($midtransStatus->transaction_status, ['expire', 'cancel', 'deny'])) {
                        // Mark the existing Snap token as invalid so a new one can be created
                        $order->payment->update(['snap_token' => null]);
                    }
                } else {
                    // Transaction not found in Midtrans - clear old Snap token
                    // This allows creating a new payment session
                    $order->payment->update(['snap_token' => null]);
                    $this->errorMessage = 'Sesi pembayaran sebelumnya tidak valid. Silakan coba bayar lagi.';
                }
            }
        } catch (\Exception $e) {
            // Check if it's a "Transaction not found" error
            $errorMessage = $e->getMessage();
            if (str_contains($errorMessage, 'Transaction doesn\'t exist') ||
                str_contains($errorMessage, '404')) {
                // Clear the old Snap token to allow creating a new one
                if ($order && $order->payment) {
                    $order->payment->update(['snap_token' => null]);
                }
                $this->errorMessage = 'Sesi pembayaran sebelumnya tidak valid. Silakan coba bayar lagi.';
                \Log::info('Cleared invalid Snap token, user can retry payment', [
                    'order_id' => $this->currentOrderId,
                ]);
            } else {
                \Log::error('Refresh Payment Status Error', [
                    'order_id' => $this->currentOrderId,
                    'error' => $errorMessage,
                ]);
            }
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
            fn(array $item): int => ((int) $item['price']) * ((int) $item['qty']),
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
     * Auto-save form state when any tracked property is updated.
     * This ensures form data persists on page reload.
     */
    public function updated($property, $value): void
    {
        // Don't save during payment modal operations
        if ($this->showPaymentModal) {
            return;
        }

        // Don't save sensitive fields or UI-only properties
        $skipProperties = [
            'snapToken',
            'showPaymentModal',
            'isLoadingPayment',
            'errorMessage',
            'orderPlaced',
            'placedOrderNumber',
            'currentOrderId',
            'cartData',
            'showAddressModal',
            'userAddresses',
        ];

        if (in_array($property, $skipProperties)) {
            return;
        }

        // Save form state
        $this->saveFormState();
    }

    /**
     * Refresh cart from session (called from frontend after localStorage sync).
     */
    public function refreshCart(): void
    {
        // Force Livewire to re-render by toggling a dummy property
        $this->dispatch('cart-refreshed');
    }

    /**
     * Validate stock for all cart items.
     * Returns array of error messages if any items have insufficient stock.
     */
    protected function validateCartStock(array $items): array
    {
        $errors = [];
        $productModel = new \App\Models\Product();

        foreach ($items as $item) {
            if (!isset($item['product_id'])) {
                continue; // Skip items without product_id (legacy items)
            }

            $product = $productModel->find($item['product_id']);

            if (!$product) {
                $errors[] = "Produk '{$item['name']}' tidak ditemukan.";
                continue;
            }

            if ($product->stock <= 0) {
                $errors[] = "'{$item['name']}' - Stok habis.";
            } elseif ($item['qty'] > $product->stock) {
                $errors[] = "'{$item['name']}' - Hanya tersedia {$product->stock} unit.";
            }
        }

        return $errors;
    }

    /**
     * Decrement stock for all items in an order.
     */
    protected function decrementOrderStock(Order $order): void
    {
        foreach ($order->items as $item) {
            if ($item->product_id && $item->product) {
                $item->product->decrement('stock', $item->quantity);
            }
        }
    }

    /**
     * Send payment success notification to customer.
     */
    protected function sendPaymentSuccessNotification(Order $order): void
    {
        $user = $order->user;

        if (!$user) {
            return;
        }

        $orderNumber = $order->order_number;
        $totalAmount = 'Rp ' . number_format($order->total_amount, 0, ',', '.');

        // Create in-app notification
        CustomerNotification::forPaymentSuccess($user->id, $orderNumber, $totalAmount);

        // Send FCM push notification
        try {
            $fcmService = app(FcmService::class);
            $fcmService->sendPaymentSuccessNotification(
                $user,
                $orderNumber,
                $totalAmount,
                $order->total_items
            );
        } catch (\Exception $e) {
            \Log::error('Failed to send payment success notification', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Dispatch browser event to refresh notification dropdown
        $this->dispatch('refresh-notifications');
    }

    public function render()
    {
        return view('livewire.checkout');
    }
}