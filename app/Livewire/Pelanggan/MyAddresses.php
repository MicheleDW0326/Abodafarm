<?php

namespace App\Livewire\Pelanggan;

use App\Models\Address;
use App\Models\DeliveryArea;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.main')]
#[Title('Alamat Saya - Akun Saya')]
class MyAddresses extends Component
{
    // Address list
    public $addresses = [];

    // Modal state
    public bool $showModal = false;
    public bool $isEditing = false;

    // 2-step wizard
    public int $currentStep = 1;
    public int $totalSteps = 2;

    // Form fields - Step 1 (Location)
    public ?float $latitude = null;
    public ?float $longitude = null;
    public string $google_place_id = '';
    public string $searchQuery = '';

    // Form fields - Step 2 (Details)
    public ?int $editingId = null;
    public string $label = '';
    public string $recipient_name = '';
    public string $phone = '';
    public string $address = '';
    public string $province = '';
    public string $city = '';
    public string $district = '';
    public string $postal_code = '';
    public string $courier_notes = '';
    public bool $is_default = false;

    // Search & Filter
    public string $searchFilter = '';
    public string $labelFilter = 'all';

    // Service area validation
    public bool $isInServiceArea = true;
    public ?string $serviceAreaMessage = null;
    public ?float $distanceToServiceCenter = null;

    // Delivery areas for service area check
    public array $deliveryAreas = [];

    // Delete confirmation
    public bool $showDeleteConfirm = false;
    public ?int $deletingId = null;

    // Copy confirmation
    public ?string $copiedAddressId = null;

    public function mount(): void
    {
        $this->loadAddresses();
        $this->loadDeliveryAreas();
    }

    public function render()
    {
        return view('livewire.pelanggan.my-addresses');
    }

    public function loadAddresses(): void
    {
        $query = Address::where('user_id', auth()->id())
            ->active()
            ->orderByDesc('is_default')
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($this->searchFilter) {
            $query->where(function ($q) {
                $q->where('recipient_name', 'like', '%' . $this->searchFilter . '%')
                    ->orWhere('address', 'like', '%' . $this->searchFilter . '%')
                    ->orWhere('phone', 'like', '%' . $this->searchFilter . '%');
            });
        }

        if ($this->labelFilter !== 'all') {
            $query->where('label', $this->labelFilter);
        }

        $this->addresses = $query->get()->toArray();
    }

    public function loadDeliveryAreas(): void
    {
        $this->deliveryAreas = DeliveryArea::active()->get()->toArray();
    }

    public function openAddModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
        $this->isEditing = false;
        $this->currentStep = 1;

        // Default center to Indonesia (Sulawesi Utara)
        if ($this->latitude === null) {
            $this->latitude = 1.5; // Default latitude
            $this->longitude = 125.0; // Default longitude
        }
    }

    public function openEditModal(int $id): void
    {
        $address = Address::where('user_id', auth()->id())->find($id);
        if (!$address) {
            return;
        }

        $this->editingId = $address->id;
        $this->label = $address->label;
        $this->recipient_name = $address->recipient_name;
        $this->phone = $address->phone;
        $this->address = $address->address;
        $this->province = $address->province;
        $this->city = $address->city;
        $this->district = $address->district;
        $this->postal_code = $address->postal_code ?? '';
        $this->courier_notes = $address->courier_notes ?? '';
        $this->is_default = $address->is_default;
        $this->latitude = $address->latitude;
        $this->longitude = $address->longitude;
        $this->google_place_id = $address->google_place_id ?? '';
        $this->isEditing = true;
        $this->showModal = true;
        $this->currentStep = 1; // Start at map step for edit too

        // Check service area
        $this->checkServiceArea();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    // Wizard Navigation
    public function nextStep(): void
    {
        if ($this->currentStep === 1) {
            // Validate location is selected
            if ($this->latitude === null || $this->longitude === null) {
                $this->addError('location', 'Pilih lokasi di peta terlebih dahulu.');
                return;
            }

            $this->resetErrorBag('location');
            $this->currentStep = 2;
        }
    }

    public function previousStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    // Location updates from JavaScript
    public function updatedLatitude(): void
    {
        $this->checkServiceArea();
    }

    public function updatedLongitude(): void
    {
        $this->checkServiceArea();
    }

    public function checkServiceArea(): void
    {
        if ($this->latitude === null || $this->longitude === null) {
            $this->isInServiceArea = true;
            $this->serviceAreaMessage = null;
            $this->distanceToServiceCenter = null;
            return;
        }

        // Check against delivery areas
        foreach ($this->deliveryAreas as $area) {
            // Simple bounding box check for performance
            // In production, you'd use proper geofencing
            if (
                stripos($this->province, $area['province'] ?? '') !== false ||
                stripos($this->city, $area['city'] ?? '') !== false ||
                stripos($area['region_name'] ?? '', $this->city) !== false
            ) {
                $this->isInServiceArea = true;
                $this->serviceAreaMessage = "Lokasi Anda berada dalam area layanan AbodaFarm.";
                $this->distanceToServiceCenter = null;
                return;
            }
        }

        // If no match found, check distance from center point (default: Talaud, Sulawesi Utara)
        $centerLat = 4.3; // Talaud approximate center
        $centerLng = 126.7;

        $distance = $this->calculateDistance($centerLat, $centerLng, $this->latitude, $this->longitude);

        // If within 150km, consider serviceable
        if ($distance <= 150) {
            $this->isInServiceArea = true;
            $this->serviceAreaMessage = "Lokasi Anda berada dalam area layanan AbodaFarm.";
            $this->distanceToServiceCenter = round($distance, 1);
        } else {
            $this->isInServiceArea = false;
            $this->serviceAreaMessage = "Mohon maaf, saat ini layanan pengiriman belum tersedia di lokasi Anda.";
            $this->distanceToServiceCenter = round($distance, 1);
        }
    }

    private function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371; // km

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lng1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lng2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos($latFrom) * cos($latTo) *
            sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    // Save address
    public function save(): void
    {
        // Validate step 2 fields
        $this->validate([
            'label' => ['required', 'string', 'max:50'],
            'recipient_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:20', 'regex:/^[\d\s\-\+]+$/'],
            'address' => ['required', 'string', 'max:500'],
            'province' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'courier_notes' => ['nullable', 'string', 'max:500'],
        ]);

        // If this is set as default, unset others first
        if ($this->is_default) {
            Address::where('user_id', auth()->id())
                ->where('id', '!=', $this->editingId)
                ->update(['is_default' => false]);
        }

        // If first address, make it default
        $existingCount = Address::where('user_id', auth()->id())->count();
        if ($existingCount === 0) {
            $this->is_default = true;
        }

        if ($this->isEditing && $this->editingId) {
            // Update existing
            $address = Address::where('user_id', auth()->id())->find($this->editingId);
            if ($address) {
                $address->update([
                    'label' => $this->label,
                    'recipient_name' => $this->recipient_name,
                    'phone' => $this->phone,
                    'address' => $this->address,
                    'province' => $this->province,
                    'city' => $this->city,
                    'district' => $this->district,
                    'postal_code' => $this->postal_code,
                    'courier_notes' => $this->courier_notes,
                    'is_default' => $this->is_default,
                    'latitude' => $this->latitude,
                    'longitude' => $this->longitude,
                    'google_place_id' => $this->google_place_id,
                ]);

                // Dispatch event for other components
                $this->dispatch('address-updated', ['addressId' => $address->id]);
            }
        } else {
            // Create new
            $newAddress = Address::create([
                'user_id' => auth()->id(),
                'label' => $this->label,
                'recipient_name' => $this->recipient_name,
                'phone' => $this->phone,
                'address' => $this->address,
                'province' => $this->province,
                'city' => $this->city,
                'district' => $this->district,
                'postal_code' => $this->postal_code,
                'courier_notes' => $this->courier_notes,
                'is_default' => $this->is_default,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'google_place_id' => $this->google_place_id,
            ]);

            // Dispatch event for other components
            $this->dispatch('address-created', ['addressId' => $newAddress->id]);
        }

        $this->loadAddresses();
        $this->closeModal();

        $this->dispatch('notify', [
            'type' => 'success',
            'title' => 'Berhasil',
            'message' => $this->isEditing ? 'Alamat berhasil diperbarui.' : 'Alamat berhasil ditambahkan.',
        ]);
    }

    // Delete address
    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteConfirm = true;
    }

    public function cancelDelete(): void
    {
        $this->deletingId = null;
        $this->showDeleteConfirm = false;
    }

    public function delete(int $id): void
    {
        $address = Address::where('user_id', auth()->id())->find($id);
        if ($address) {
            $wasDefault = $address->is_default;
            $address->delete();

            // If deleted address was default, make first remaining address as default
            if ($wasDefault) {
                $firstAddress = Address::where('user_id', auth()->id())->first();
                if ($firstAddress) {
                    $firstAddress->update(['is_default' => true]);
                }
            }

            $this->loadAddresses();
            $this->showDeleteConfirm = false;
            $this->deletingId = null;

            // Dispatch event
            $this->dispatch('address-deleted', ['addressId' => $id]);

            $this->dispatch('notify', [
                'type' => 'success',
                'title' => 'Berhasil',
                'message' => 'Alamat berhasil dihapus.',
            ]);
        }
    }

    // Set as default
    public function setAsDefault(int $id): void
    {
        $address = Address::where('user_id', auth()->id())->find($id);
        if ($address) {
            $address->setAsDefault();
            $this->loadAddresses();

            $this->dispatch('address-updated', ['addressId' => $id]);

            $this->dispatch('notify', [
                'type' => 'success',
                'title' => 'Berhasil',
                'message' => 'Alamat utama berhasil diperbarui.',
            ]);
        }
    }

    // Copy address to clipboard
    public function copyAddress(int $id): void
    {
        $address = Address::where('user_id', auth()->id())->find($id);
        if ($address) {
            $fullAddress = $address->formatted_address;

            // Dispatch event to JavaScript to copy to clipboard
            $this->dispatch('copy-to-clipboard', ['text' => $fullAddress]);

            $this->copiedAddressId = $id;

            $this->dispatch('notify', [
                'type' => 'success',
                'title' => 'Disalin',
                'message' => 'Alamat berhasil disalin ke clipboard.',
            ]);

            // Reset after 2 seconds
            $this->dispatch('reset-copy-status')->self();
        }
    }

    public function resetCopyStatus(): void
    {
        $this->copiedAddressId = null;
    }

    // Get single address for display
    public function getAddressById(int $id): ?array
    {
        return collect($this->addresses)->firstWhere('id', $id);
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->label = '';
        $this->recipient_name = '';
        $this->phone = '';
        $this->address = '';
        $this->province = '';
        $this->city = '';
        $this->district = '';
        $this->postal_code = '';
        $this->courier_notes = '';
        $this->is_default = false;
        $this->latitude = null;
        $this->longitude = null;
        $this->google_place_id = '';
        $this->searchQuery = '';
        $this->isEditing = false;
        $this->currentStep = 1;
        $this->isInServiceArea = true;
        $this->serviceAreaMessage = null;
        $this->distanceToServiceCenter = null;
        $this->showDeleteConfirm = false;
        $this->deletingId = null;
        $this->copiedAddressId = null;
        $this->resetValidation();
    }

    // Real-time validation helpers
    public function updatedSearchQuery(): void
    {
        // This will be handled by JS for autocomplete
    }

    // Filter methods
    public function updatedSearchFilter(): void
    {
        $this->loadAddresses();
    }

    public function updatedLabelFilter(): void
    {
        $this->loadAddresses();
    }

    public function clearFilters(): void
    {
        $this->searchFilter = '';
        $this->labelFilter = 'all';
        $this->loadAddresses();
    }
}
