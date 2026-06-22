<?php

namespace App\Livewire\Peternak;

use App\Models\FarmProfile;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.main')]
#[Title('Profil Peternakan')]
class FarmProfileSettings extends Component
{
    use WithFileUploads;

    public ?FarmProfile $profileModel = null;
    public string $farmName = '';
    public string $description = '';
    public string $phone = '';
    public string $village = '';
    public string $district = '';
    public string $regency = '';
    public string $province = '';
    public string $address = '';
    public $image = null;
    public ?string $existingImage = null;
    public bool $showSuccess = false;

    public function mount()
    {
        $this->loadProfile();
    }

    public function loadProfile()
    {
        $this->profileModel = FarmProfile::where('user_id', auth()->id())->first();

        if ($this->profileModel) {
            $this->farmName = $this->profileModel->farm_name;
            $this->description = $this->profileModel->description ?? '';
            $this->phone = $this->profileModel->phone ?? '';
            $this->village = $this->profileModel->village ?? '';
            $this->district = $this->profileModel->district ?? '';
            $this->regency = $this->profileModel->regency ?? '';
            $this->province = $this->profileModel->province ?? '';
            $this->address = $this->profileModel->address ?? '';
            $this->existingImage = $this->profileModel->image;
        }
    }

    public function save()
    {
        $this->validate([
            'farmName' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        try {
            $data = [
                'user_id' => auth()->id(),
                'farm_name' => $this->farmName,
                'description' => $this->description ?: null,
                'phone' => $this->phone ?: null,
                'village' => $this->village ?: null,
                'district' => $this->district ?: null,
                'regency' => $this->regency ?: null,
                'province' => $this->province ?: null,
                'address' => $this->address ?: null,
            ];

            if ($this->image) {
                if ($this->existingImage) {
                    Storage::disk('public')->delete($this->existingImage);
                }
                $data['image'] = $this->image->store('farm-profiles', 'public');
            }

            FarmProfile::updateOrCreate(
                ['user_id' => auth()->id()],
                $data
            );

            $this->showSuccess = true;
            $this->loadProfile();

            session()->flash('success', 'Profil peternakan berhasil disimpan!');

            $this->dispatch('saved');
        } catch (\Exception $e) {
            session()->flash('error', 'Error: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.peternak.farm-profile');
    }
}
