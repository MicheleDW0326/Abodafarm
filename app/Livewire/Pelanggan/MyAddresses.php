<?php

namespace App\Livewire\Pelanggan;

use App\Models\Address;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.main')]
#[Title('Alamat Saya - Akun Saya')]
class MyAddresses extends Component
{
    public $addresses = [];
    public bool $showModal = false;
    public bool $isEditing = false;

    // Form fields
    public ?int $editingId = null;
    public string $label = '';
    public string $recipient_name = '';
    public string $phone = '';
    public string $address = '';
    public string $province = '';
    public string $city = '';
    public string $district = '';
    public string $postal_code = '';
    public bool $is_default = false;
    public ?float $latitude = null;
    public ?float $longitude = null;
    public string $google_place_id = '';

    public function mount(): void
    {
        $this->loadAddresses();
    }

    public function render()
    {
        return view('livewire.pelanggan.my-addresses');
    }

    public function loadAddresses(): void
    {
        $this->addresses = Address::where('user_id', auth()->id())
            ->orderByDesc('is_default')
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    public function openAddModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
        $this->isEditing = false;
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
        $this->is_default = $address->is_default;
        $this->latitude = $address->latitude;
        $this->longitude = $address->longitude;
        $this->google_place_id = $address->google_place_id ?? '';
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function save(): void
    {
        $this->validate([
            'label' => ['required', 'string', 'max:50'],
            'recipient_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string'],
            'province' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:10'],
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
                    'is_default' => $this->is_default,
                    'latitude' => $this->latitude,
                    'longitude' => $this->longitude,
                    'google_place_id' => $this->google_place_id,
                ]);
            }
        } else {
            // Create new
            Address::create([
                'user_id' => auth()->id(),
                'label' => $this->label,
                'recipient_name' => $this->recipient_name,
                'phone' => $this->phone,
                'address' => $this->address,
                'province' => $this->province,
                'city' => $this->city,
                'district' => $this->district,
                'postal_code' => $this->postal_code,
                'is_default' => $this->is_default,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'google_place_id' => $this->google_place_id,
            ]);
        }

        $this->loadAddresses();
        $this->closeModal();

        $this->dispatch('notify', [
            'type' => 'success',
            'title' => 'Berhasil',
            'message' => $this->isEditing ? 'Alamat berhasil diperbarui.' : 'Alamat berhasil ditambahkan.',
        ]);
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

            $this->dispatch('notify', [
                'type' => 'success',
                'title' => 'Berhasil',
                'message' => 'Alamat berhasil dihapus.',
            ]);
        }
    }

    public function setAsDefault(int $id): void
    {
        $address = Address::where('user_id', auth()->id())->find($id);
        if ($address) {
            $address->setAsDefault();
            $this->loadAddresses();

            $this->dispatch('notify', [
                'type' => 'success',
                'title' => 'Berhasil',
                'message' => 'Alamat utama berhasil diperbarui.',
            ]);
        }
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
        $this->is_default = false;
        $this->latitude = null;
        $this->longitude = null;
        $this->google_place_id = '';
        $this->isEditing = false;
        $this->resetValidation();
    }
}
