<?php

namespace App\Livewire\Home;

use App\Models\{Address, Order, OrderItem, Product};
use App\Models\PeternakNotification;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithFileUploads;

class Products extends Component
{
    use WithFileUploads;

    /** @var array<int, array<string,mixed>> */
    public array $productsLive = [];

    /** @var array<int, array<string,mixed>> */
    public array $productsFresh = [];

    // Booking form properties
    public bool $showBookingModal = false;
    public ?array $bookingProduct = null;
    public string $bookingName = '';
    public string $bookingPhone = '';
    public string $bookingNotes = '';

    // Address properties
    public array $userAddresses = [];
    public ?int $selectedAddressId = null;
    public bool $showNewAddressForm = false;
    public string $newAddressLabel = '';
    public string $newAddress = '';
    public string $newProvince = '';
    public string $newCity = '';
    public string $newDistrict = '';
    public string $newPostalCode = '';
    public ?float $newLatitude = null;
    public ?float $newLongitude = null;
    public string $newGooglePlaceId = '';

    public bool $isSubmitting = false;

    public function mount(array $productsLive = [], array $productsFresh = []): void
    {
        $this->productsLive = $productsLive;
        $this->productsFresh = $productsFresh;
    }

    public function addToCart(int $index): void
    {
        if (! isset($this->productsFresh[$index])) {
            return;
        }

        $this->dispatch('add-to-cart', product: $this->productsFresh[$index]);
    }

    public function showProductDetail(int $productId): void
    {
        $this->dispatch('show-product-detail', productId: $productId);
    }

    // Booking methods
    public function openBookingModal(array $product): void
    {
        if (!auth()->check()) {
            $this->dispatch('open-login-prompt', product: $product);
            return;
        }

        $this->bookingProduct = $product;
        $this->showBookingModal = true;
        $this->loadUserAddresses();
        $this->resetForm();
    }

    public function loadUserAddresses(): void
    {
        $user = auth()->user();

        $this->userAddresses = Address::where('user_id', $user->id)
            ->orderByDesc('is_default')
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();

        // Auto-fill from user profile
        $this->bookingName = $user->name ?? '';
        $this->bookingPhone = $user->phone ?? '';

        // Set default selected address
        $defaultAddress = collect($this->userAddresses)->firstWhere('is_default', true);
        if ($defaultAddress) {
            $this->selectedAddressId = $defaultAddress['id'];
            $this->showNewAddressForm = false;
            // Auto-fill phone from address if user phone is empty
            if (empty($this->bookingPhone) && !empty($defaultAddress['phone'])) {
                $this->bookingPhone = $defaultAddress['phone'];
            }
        } else {
            $this->selectedAddressId = null;
            $this->showNewAddressForm = empty($this->userAddresses);
        }
    }

    public function closeBookingModal(): void
    {
        $this->showBookingModal = false;
        $this->bookingProduct = null;
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $user = auth()->user();
        $this->bookingName = $user?->name ?? '';
        $this->bookingPhone = $user?->phone ?? '';
        $this->bookingNotes = '';
        $this->selectedAddressId = null;
        $this->showNewAddressForm = false;
        $this->newAddressLabel = '';
        $this->newAddress = '';
        $this->newProvince = '';
        $this->newCity = '';
        $this->newDistrict = '';
        $this->newPostalCode = '';
        $this->newLatitude = null;
        $this->newLongitude = null;
        $this->newGooglePlaceId = '';
        $this->isSubmitting = false;
        $this->resetValidation();
    }

    public function selectExistingAddress(int $addressId): void
    {
        $this->selectedAddressId = $addressId;
        $this->showNewAddressForm = false;
    }

    public function showAddNewAddress(): void
    {
        $this->selectedAddressId = null;
        $this->showNewAddressForm = true;
    }

    public function saveNewAddress(): void
    {
        $this->validate([
            'newAddressLabel' => 'required|string|max:50',
            'newAddress' => 'required|string|max:500',
            'newProvince' => 'required|string|max:100',
            'newCity' => 'required|string|max:100',
            'newDistrict' => 'required|string|max:100',
            'newPostalCode' => 'nullable|string|max:10',
        ], [
            'newAddressLabel.required' => 'Label alamat harus diisi (contoh: Rumah, Kantor)',
            'newAddress.required' => 'Alamat lengkap harus diisi',
            'newProvince.required' => 'Provinsi harus diisi',
            'newCity.required' => 'Kota/Kabupaten harus diisi',
            'newDistrict.required' => 'Kecamatan harus diisi',
        ]);

        // If first address, make it default
        $isFirstAddress = Address::where('user_id', auth()->id())->count() === 0;

        $address = Address::create([
            'user_id' => auth()->id(),
            'label' => $this->newAddressLabel,
            'recipient_name' => auth()->user()->name,
            'phone' => $this->bookingPhone ?: auth()->user()->phone ?? '',
            'address' => $this->newAddress,
            'province' => $this->newProvince,
            'city' => $this->newCity,
            'district' => $this->newDistrict,
            'postal_code' => $this->newPostalCode,
            'latitude' => $this->newLatitude,
            'longitude' => $this->newLongitude,
            'google_place_id' => $this->newGooglePlaceId,
            'is_default' => $isFirstAddress,
        ]);

        // Reload addresses and select the new one
        $this->loadUserAddresses();
        $this->selectedAddressId = $address->id;
        $this->showNewAddressForm = false;

        $this->dispatch('alert', type: 'success', message: 'Alamat berhasil disimpan.');
    }

    public function submitBooking(): void
    {
        if (!auth()->check()) {
            $this->dispatch('alert', type: 'error', message: 'Silakan login terlebih dahulu untuk melakukan booking.');
            return;
        }

        // Validation
        $this->validate([
            'bookingName' => 'required|string|max:255',
            'bookingPhone' => 'required|string|max:20',
            'bookingNotes' => 'nullable|string|max:1000',
        ], [
            'bookingName.required' => 'Nama lengkap harus diisi',
            'bookingPhone.required' => 'Nomor WhatsApp harus diisi',
        ]);

        // Validate address selection
        if (!$this->selectedAddressId && !$this->showNewAddressForm) {
            $this->addError('selectedAddressId', 'Silakan pilih atau tambahkan alamat pengiriman.');
            return;
        }

        if ($this->showNewAddressForm && empty($this->newAddress)) {
            $this->addError('newAddress', 'Alamat lengkap harus diisi.');
            return;
        }

        $this->isSubmitting = true;

        try {
            $product = Product::find($this->bookingProduct['id']);

            if (!$product) {
                $this->dispatch('alert', type: 'error', message: 'Produk tidak ditemukan.');
                $this->isSubmitting = false;
                return;
            }

            // Get selected address
            $address = null;
            $fullAddress = '';

            if ($this->selectedAddressId) {
                $address = Address::where('user_id', auth()->id())->find($this->selectedAddressId);
            }

            if ($address) {
                $fullAddress = $address->full_address;
            } elseif ($this->showNewAddressForm) {
                $fullAddress = "{$this->newAddress}, {$this->newDistrict}, {$this->newCity}, {$this->newProvince}" .
                    ($this->newPostalCode ? ", {$this->newPostalCode}" : '');
            }

            // Create booking order
            $order = Order::create([
                'order_number' => Order::generateBookingCode(),
                'user_id' => auth()->id(),
                'customer_name' => $this->bookingName,
                'customer_phone' => $this->bookingPhone,
                'customer_address' => $fullAddress,
                'notes' => $this->bookingNotes,
                'total_items' => 1,
                'total_amount' => $product->price,
                'status' => Order::STATUS_PENDING,
                'placed_at' => now(),
                'order_type' => Order::TYPE_INQUIRY,
            ]);

            // Create order item
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_image' => $product->image,
                'unit_price' => $product->price,
                'quantity' => 1,
                'subtotal' => $product->price,
            ]);

            // Update address phone if different
            if ($address && $address->phone !== $this->bookingPhone) {
                $address->update(['phone' => $this->bookingPhone]);
            }

            // Notify the farmer/owner
            if ($product->owner_id) {
                PeternakNotification::create([
                    'user_id' => $product->owner_id,
                    'type' => 'new_booking',
                    'title' => 'Booking Baru',
                    'message' => "Ada booking baru untuk {$product->name} dari {$this->bookingName}.",
                    'data' => json_encode([
                        'booking_id' => $order->id,
                        'order_number' => $order->order_number,
                        'product_id' => $product->id,
                    ]),
                    'is_read' => false,
                ]);
            }

            $this->dispatch('alert', type: 'success', message: 'Booking berhasil diajukan! Peternak akan segera menghubungi Anda.');
            $this->closeBookingModal();

        } catch (\Exception $e) {
            \Log::error('Booking submission error: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'product_id' => $this->bookingProduct['id'] ?? null,
                'trace' => $e->getTraceAsString(),
            ]);
            $this->dispatch('alert', type: 'error', message: 'Terjadi kesalahan: ' . $e->getMessage());
        } finally {
            $this->isSubmitting = false;
        }
    }

    public function render()
    {
        return view('livewire.home.products');
    }

    public function placeholder(): \Illuminate\View\View
    {
        return view('livewire.home.products-skeleton');
    }
}