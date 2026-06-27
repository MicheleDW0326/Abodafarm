<?php

namespace App\Livewire\Peternak;

use App\Models\FarmGallery;
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

    // Images
    public $image = null;
    public ?string $existingImage = null;
    public $coverImage = null;
    public ?string $existingCoverImage = null;
    public $logo = null;
    public ?string $existingLogo = null;

    // Location
    public ?float $latitude = null;
    public ?float $longitude = null;
    public string $googlePlaceId = '';
    public string $searchQuery = '';

    // Gallery
    public $newGalleryImages = [];
    public array $galleries = [];

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
            $this->existingCoverImage = $this->profileModel->cover_image;
            $this->existingLogo = $this->profileModel->logo;
            $this->latitude = $this->profileModel->latitude;
            $this->longitude = $this->profileModel->longitude;
            $this->googlePlaceId = $this->profileModel->google_place_id ?? '';
            $this->galleries = $this->profileModel->galleries()->get()->toArray();
        }
    }

    public function updatedLatitude(): void
    {
        // Called when Livewire updates latitude
    }

    public function updatedLongitude(): void
    {
        // Called when Livewire updates longitude
    }

    public function save()
    {
        $this->validate([
            'farmName' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'newGalleryImages.*' => 'image|max:2048',
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
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'google_place_id' => $this->googlePlaceId ?: null,
            ];

            // Handle main image
            if ($this->image) {
                if ($this->existingImage) {
                    Storage::disk('public')->delete($this->existingImage);
                }
                $data['image'] = $this->image->store('farm-profiles', 'public');
            }

            // Handle cover image
            if ($this->coverImage) {
                if ($this->existingCoverImage) {
                    Storage::disk('public')->delete($this->existingCoverImage);
                }
                $data['cover_image'] = $this->coverImage->store('farm-profiles/covers', 'public');
            }

            // Handle logo
            if ($this->logo) {
                if ($this->existingLogo) {
                    Storage::disk('public')->delete($this->existingLogo);
                }
                $data['logo'] = $this->logo->store('farm-profiles/logos', 'public');
            }

            $profile = FarmProfile::updateOrCreate(
                ['user_id' => auth()->id()],
                $data
            );

            // Handle gallery images
            foreach ($this->newGalleryImages as $image) {
                $path = $image->store('farm-galleries', 'public');
                $profile->galleries()->create([
                    'image' => $path,
                    'sort_order' => $profile->galleries()->max('sort_order') + 1,
                ]);
            }

            $this->showSuccess = true;
            $this->loadProfile();

            session()->flash('success', 'Profil peternakan berhasil disimpan!');

            $this->dispatch('saved');
        } catch (\Exception $e) {
            session()->flash('error', 'Error: ' . $e->getMessage());
        }
    }

    public function deleteImage(string $type = 'image'): void
    {
        if ($type === 'cover' && $this->existingCoverImage) {
            Storage::disk('public')->delete($this->existingCoverImage);
            $this->profileModel->update(['cover_image' => null]);
            $this->existingCoverImage = null;
        } elseif ($type === 'logo' && $this->existingLogo) {
            Storage::disk('public')->delete($this->existingLogo);
            $this->profileModel->update(['logo' => null]);
            $this->existingLogo = null;
        } elseif ($type === 'image' && $this->existingImage) {
            Storage::disk('public')->delete($this->existingImage);
            $this->profileModel->update(['image' => null]);
            $this->existingImage = null;
        }
    }

    public function clearLocation(): void
    {
        $this->latitude = null;
        $this->longitude = null;
        $this->googlePlaceId = '';
    }

    public function deleteGallery(int $id): void
    {
        $gallery = FarmGallery::find($id);
        if ($gallery && $this->profileModel && $gallery->farm_profile_id === $this->profileModel->id) {
            Storage::disk('public')->delete($gallery->image);
            $gallery->delete();
            $this->galleries = $this->profileModel->fresh()->galleries()->get()->toArray();
        }
    }

    public function render()
    {
        return view('livewire.peternak.farm-profile');
    }
}
