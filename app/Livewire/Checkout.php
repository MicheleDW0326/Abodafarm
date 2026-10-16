<?php

namespace App\Livewire;

use App\Enums\PaymentStatusEnum;
use App\Events\ClearCheckoutSession;
use App\Events\PaymentReceived;
use App\Models\Address;
use App\Models\CustomerNotification;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Models\Promotion;
use App\Services\FcmService;
use App\Services\MidtransService;
use App\Services\OrderStateMachine;
use App\Services\PromoService;
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

    protected $listeners = [
        'echo:checkout.{currentUserId},checkout.session-cleared' => 'onSessionCleared',
    ];
    public function getCurrentUserIdProperty(): int
    {
        return auth()->id();
    }
    public function onSessionCleared(): void
    {
        $this->clearCheckoutSession();
    }

    public string $name = '';
    public string $phone = '';
    public string $address = '';
    public string $notes = '';
    public ?string $cartData = null;
    public bool $orderPlaced = false;
    public ?string $placedOrderNumber = null;
    public bool $showPaymentModal = false;
    public bool $isLoadingPayment = false;
    public ?string $snapToken = null;
    public ?int $currentOrderId = null;
    public ?string $errorMessage = null;
    public array $userAddresses = [];
    public ?int $selectedAddressId = null;
    public bool $useNewAddress = false;
    public string $newAddressLabel = '';
    public string $newFullAddress = '';
    public string $newProvince = '';
    public string $newCity = '';
    public string $newDistrict = '';
    public string $newPostalCode = '';
    public bool $isInServiceArea = true;
    public ?string $serviceAreaMessage = null;
    public bool $showAddressModal = false;
    public string $promoCode = '';
    public ?string $appliedPromoCode = null;
    public ?string $promoError = null;
    public ?string $promoSuccess = null;
    public ?float $discountAmount = null;
    public ?string $promoType = null;
    public ?string $promoName = null;
    public function mount(): void
    {
        // Require authentication for checkout
        if (!auth()->check()) {
            Session::put('url.intended', '/checkout');
            $this->redirect(route('login'), navigate: true);
            return;
        }
        // Auto-fill user info if available
        $user = auth()->user();
        if ($user && empty($this->name)) {
            $this->name = $user->name;
        }
        if ($user && empty($this->phone)) {
            $this->phone = $user->phone ?? '';
        }
        // Restore form state from session if exists
        $this->restoreFormState();

        // Check for saved order ID from session
        $savedOrderId = Session::get(self::ORDER_ID_KEY);
        $cartItems = $this->getCartItems();
        $hasItemsInCart = !empty($cartItems);

        if ($savedOrderId) {
            $this->currentOrderId = $savedOrderId;
            $order = Order::with('payment')->find($savedOrderId);

            if ($order) {
                // If cart is empty AND previous order is completed, clear session and start fresh
                if (!$hasItemsInCart && $order->payment && $order->payment->isCompleted()) {
                    $this->clearCheckoutSession();
                    $this->restoreFormState();
                }
                // If payment is pending, restore the order form
                elseif ($order->payment_status !== \App\Enums\PaymentStatusEnum::PAID) {
                    $this->placedOrderNumber = $order->order_number;
                    $this->orderPlaced = true;
                }
                // If cart has items, keep session to allow re-payment or continue
                elseif ($hasItemsInCart) {
                    $this->placedOrderNumber = $order->order_number;
                    $this->orderPlaced = true;
                }
            }
        }

        // Restore applied promo code
        $this->restorePromoState();

        $this->loadUserAddresses();
    }
    protected function restorePromoState(): void
    {
        $promoService = new PromoService();
        $applied = $promoService->getApplied();

        if ($applied && isset($applied['code'])) {
            $promotion = Promotion::where('code', $applied['code'])->first();
            if ($promotion && $promotion->status === 'active') {
                $this->appliedPromoCode = $applied['code'];
                $this->discountAmount = (float) ($applied['discount_amount'] ?? 0);
                $this->promoType = $applied['type'] ?? null;
                $this->promoName = $applied['name'] ?? null;
            } else {
                $promoService->remove();
            }
        }
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

    /**
     * Clear entire checkout session state.
     * Called when payment is completed or when starting fresh checkout.
     */
    protected function clearCheckoutSession(): void
    {
        Session::forget(self::CHECKOUT_FORM_KEY);
        Session::forget(self::ORDER_ID_KEY);
        Session::forget(self::CART_SESSION_KEY);

        // Reset all properties to initial state
        $this->currentOrderId = null;
        $this->placedOrderNumber = null;
        $this->orderPlaced = false;
        $this->showPaymentModal = false;
        $this->snapToken = null;
        $this->appliedPromoCode = null;
        $this->discountAmount = null;
        $this->promoType = null;
        $this->promoName = null;
        $this->promoError = null;
        $this->promoSuccess = null;
    }

    public function loadUserAddresses(): void
    {
        if (!auth()->check()) {
            $this->userAddresses = [];
            return;
        }

        // OPTIMIZED: Added limit to prevent loading too many addresses
        $this->userAddresses = Address::where('user_id', auth()->id())
            ->orderByDesc('is_default')
            ->orderBy('created_at', 'desc')
            ->limit(10) // Limit to 10 most recent addresses for performance
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

    // Promo Code Methods
    public function applyPromoCode(): void
    {
        $this->promoError = null;
        $this->promoSuccess = null;

        if (empty(trim($this->promoCode))) {
            $this->promoError = 'Masukkan kode promo.';
            return;
        }

        $userId = auth()->id();
        $cart = ['items' => $this->getCartItems()];
        $shipping = 20000; // Default shipping estimate

        $promoService = new PromoService();
        $result = $promoService->apply(trim($this->promoCode), $cart, $userId, $shipping);

        if (!$result['valid']) {
            $this->promoError = $result['message'];
            $this->appliedPromoCode = null;
            $this->discountAmount = null;
            $this->promoType = null;
            $this->promoName = null;
            return;
        }

        $this->appliedPromoCode = $result['promo']['code'];
        $this->discountAmount = $result['discount'];
        $this->promoType = $result['promo']['type'] ?? null;
        $this->promoName = $result['promo']['name'] ?? null;
        $this->promoSuccess = $result['message'];
        $this->promoCode = '';

        // Dispatch browser event to update Alpine state
        $this->dispatch('promo-applied', [
            'discount' => $this->discountAmount,
            'type' => $this->promoType,
            'code' => $this->appliedPromoCode,
            'name' => $this->promoName,
        ]);
    }

    public function removePromoCode(): void
    {
        $promoService = new PromoService();
        $promoService->remove();

        $this->appliedPromoCode = null;
        $this->discountAmount = null;
        $this->promoType = null;
        $this->promoName = null;
        $this->promoSuccess = 'Kode promo dihapus.';

        // Dispatch browser event to update Alpine state
        $this->dispatch('promo-removed');
    }

    public function placeOrder(?string $cartJson = null): void
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

        // Get cart items - use passed JSON or fallback to stored cartData
        $items = [];
        if ($cartJson) {
            $items = json_decode($cartJson, true);
        }
        if (empty($items)) {
            $items = $this->getCartItems();
        }

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

        // SERVER-SIDE VALIDATION: Re-validate promo before creating order
        // This prevents manual manipulation from frontend
        if ($this->appliedPromoCode) {
            $promoService = new PromoService();
            $cart = ['items' => $items];
            $promoValidation = $promoService->validateBeforePayment($cart, auth()->id());

            if (!$promoValidation['valid']) {
                $this->promoError = $promoValidation['message'];
                $this->appliedPromoCode = null;
                $this->discountAmount = null;
                $this->promoType = null;
                $this->promoName = null;
                $promoService->remove();
                return;
            }

            // Update discount amount with recalculated value
            $this->discountAmount = $promoValidation['discount'];
        }

        $order = DB::transaction(function () use ($items): Order {
            // Calculate final total with discount
            $subtotal = $this->cartTotal();
            $shippingCost = $this->shippingCost();
            $discount = $this->discountAmount ?? 0;
            $finalTotal = max(0, $subtotal + $shippingCost - $discount);

            $order = Order::create([
                'order_number' => 'FG-' . now()->format('YmdHis') . '-' . str_pad((string) random_int(0, 999), 3, '0', STR_PAD_LEFT),
                'user_id' => auth()->id(),
                'customer_name' => $this->name,
                'customer_phone' => $this->phone,
                'customer_address' => $this->address,
                'notes' => $this->notes,
                'total_items' => $this->cartCount(),
                'total_amount' => $finalTotal, // Use discounted total with shipping
                'status' => 'pending',
                'placed_at' => now(),
                'order_type' => Order::TYPE_CART,
            ]);

            $order->items()->createMany(array_map(
                static fn(array $item): array => [
                    'product_id' => $item['product_id'] ?? null,
                    'product_name' => $item['name'],
                    'product_image' => $item['image'] ?? null,
                    'unit_price' => (int) $item['price'],
                    'quantity' => (int) $item['qty'],
                    'subtotal' => ((int) $item['price']) * ((int) $item['qty']),
                ],
                $items
            ));

            // Store discount info on order for reference
            if ($this->appliedPromoCode && $discount > 0) {
                $order->update([
                    'notes' => ($this->notes ? $this->notes . "\n" : '') .
                        "Kode Promo: {$this->appliedPromoCode} (-Rp " . number_format($discount, 0, ',', '.') . ")",
                ]);
            }

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

        $this->isLoadingPayment = true;
        $this->errorMessage = null;
        $this->snapToken = null;

        try {
            // OPTIMIZED: Single query with eager loading instead of duplicate queries
            $order = Order::with(['items.product', 'payment'])->find($this->currentOrderId);

            if (!$order) {
                $this->errorMessage = 'Order tidak ditemukan.';
                $this->isLoadingPayment = false;
                return;
            }

            // Check if payment already exists - use eager loaded relation
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
            \Log::error('Midtrans Snap Token Error', [
                'order_id' => $this->currentOrderId,
                'error' => $e->getMessage(),
            ]);
            $this->errorMessage = 'Gagal membuat token pembayaran. Silakan coba beberapa saat lagi.';
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
                $midtransStatus = MidtransService::checkTransactionStatus($order->order_number);

                if ($midtransStatus) {
                    $order->payment->update([
                        'transaction_id' => $midtransStatus->transaction_id ?? $order->payment->transaction_id,
                        'payment_type' => $midtransStatus->payment_type ?? $order->payment->payment_type,
                        'transaction_status' => $midtransStatus->transaction_status ?? $order->payment->transaction_status,
                        'fraud_status' => $midtransStatus->fraud_status ?? $order->payment->fraud_status,
                    ]);
                    if (in_array($midtransStatus->transaction_status, ['capture', 'settlement'])) {
                        $alreadyPaid = $order->payment && $order->payment->paid_at !== null;

                        $order->payment->update(['paid_at' => now()]);
                        OrderStateMachine::setPaymentStatus(
                            $order,
                            PaymentStatusEnum::PAID,
                            OrderStatusLog::TRIGGER_WEBHOOK,
                            auth()->id(),
                            'Pembayaran berhasil via Midtrans'
                        );
                        if (!$alreadyPaid && $order->order_type === Order::TYPE_CART) {
                            $this->decrementOrderStock($order);
                        }
                        $this->recordPromotionUsage($order);
                        $this->sendPaymentSuccessNotification($order);
                        $this->clearCheckoutSession();

                        $order->refresh();
                        $order->payment->refresh();
                    }
                    if (in_array($midtransStatus->transaction_status, ['expire', 'cancel', 'deny'])) {
                        $order->payment->update(['snap_token' => null]);
                    }
                } else {
                    $order->payment->update(['snap_token' => null]);
                    $this->errorMessage = 'Sesi pembayaran sebelumnya tidak valid. Silakan coba bayar lagi.';
                }
            }
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            if (
                str_contains($errorMessage, 'Transaction doesn\'t exist') ||
                str_contains($errorMessage, '404')
            ) {
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
                $this->errorMessage = 'Gagal memuat status pembayaran. Silakan coba beberapa saat lagi.';
            }
        }
    }
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

        // Priority 2: cart data from request (Livewire wire:model)
        $requestCartData = request()->input('cartData');
        if (!empty($requestCartData)) {
            $decoded = json_decode($requestCartData, true);
            if (is_array($decoded) && !empty($decoded)) {
                return $decoded;
            }
        }

        // Priority 3: session cart
        $sessionCart = session(self::CART_SESSION_KEY, []);
        if (!empty($sessionCart)) {
            return array_values($sessionCart);
        }

        return [];
    }

    public function cartCount(): int
    {
        $items = $this->getCartItems();
        return array_sum(array_column($items, 'qty'));
    }

    public function cartTotal(): int
    {
        $items = $this->getCartItems();
        return array_sum(array_map(
            fn(array $item): int => ((int) $item['price']) * ((int) $item['qty']),
            $items
        ));
    }

    public function shippingCost(): int
    {
        // Free shipping if promo type is 'free_shipping'
        if ($this->promoType === 'free_shipping') {
            return 0;
        }
        return 20000;
    }

    public function discountedTotal(): int
    {
        $subtotal = $this->cartTotal();
        $shipping = $this->shippingCost();
        $discount = $this->discountAmount ?? 0;

        return max(0, $subtotal + $shipping - $discount);
    }

    public function totalSavings(): int
    {
        $discount = $this->discountAmount ?? 0;
        if ($this->promoType === 'free_shipping') {
            $discount += 20000;
        }
        return $discount;
    }
    public function getMidtransClientKey(): string
    {
        return config('services.midtrans.client_key', '');
    }

    public function updated($property, $value): void
    {
        if ($this->showPaymentModal) {
            return;
        }
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
     * Refresh cart from hidden input (called from frontend after localStorage sync).
     */
    public function refreshCart(): void
    {
        // Re-read cartData from request (hidden input)
        $this->cartData = request()->input('cartData', '');

        // Force Livewire to re-render
        $this->dispatch('cart-refreshed');
    }

    /**
     * Validate stock for all cart items.
     * Returns array of error messages if any items have insufficient stock.
     * OPTIMIZED: Single query for all products instead of N+1.
     */
    protected function validateCartStock(array $items): array
    {
        $errors = [];

        // Get all product IDs from cart
        $productIds = collect($items)
            ->pluck('product_id')
            ->filter()
            ->unique()
            ->toArray();

        if (empty($productIds)) {
            return $errors;
        }

        // Single query to get all products
        $products = \App\Models\Product::whereIn('id', $productIds)->get()->keyBy('id');

        foreach ($items as $item) {
            if (!isset($item['product_id'])) {
                continue; // Skip items without product_id (legacy items)
            }

            $product = $products->get($item['product_id']);

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
     * OPTIMIZED: Batch update instead of N+1 queries.
     */
    protected function decrementOrderStock(Order $order): void
    {
        // Eager load products if not already loaded
        $order->loadMissing('items.product');

        // Collect product updates
        $productQuantities = [];
        foreach ($order->items as $item) {
            if ($item->product_id && $item->product) {
                $productQuantities[$item->product_id] = ($productQuantities[$item->product_id] ?? 0) + $item->quantity;
            }
        }

        // Batch decrement each product by the total quantity ordered
        foreach ($productQuantities as $productId => $totalQuantity) {
            \App\Models\Product::where('id', $productId)->decrement('stock', $totalQuantity);
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

    /**
     * Record promotion usage after successful payment.
     */
    protected function recordPromotionUsage(Order $order): void
    {
        $promoService = new PromoService();
        $applied = $promoService->getApplied();

        if (!$applied || !$this->appliedPromoCode || !isset($applied['code'])) {
            return;
        }

        $promotion = Promotion::where('code', $applied['code'])->first();

        if (!$promotion) {
            return;
        }

        $promoService->markAsUsed(
            $promotion->id,
            auth()->id(),
            $order->id,
            $this->discountAmount ?? 0
        );

        // Clear applied promo from session
        $promoService->remove();
        $this->appliedPromoCode = null;
        $this->discountAmount = null;
        $this->promoType = null;
        $this->promoName = null;
    }

    /**
     * Submit checkout form via wire:submit.
     * This method is called by Livewire's form submission mechanism.
     */
    public function submitCheckout(): void
    {
        // Get cart data from the hidden input or property
        $cartJson = $this->cartData;

        // Call placeOrder with the cart data
        $this->placeOrder($cartJson);
    }

    public function render()
    {
        return view('livewire.checkout');
    }
}
