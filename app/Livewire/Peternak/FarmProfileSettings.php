<?php

namespace App\Livewire\Peternak;

use App\Models\FarmGallery;
use App\Models\FarmProfile;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;

#[Layout('layouts.main')]
#[Title('Profil Peternakan')]
class FarmProfileSettings extends Component
{
    use WithFileUploads;

    // Store only the ID to avoid Eloquent hydration issues
    protected ?int $profileModelId = null;

    // Extracted profile data for blade (avoid passing model directly)
    public ?int $profileCompletion = 0;
    public ?array $missingFields = [];
    public bool $isVerified = false;
    public array $stats = [
        'active_products' => 0,
        'completed_bookings' => 0,
        'rating' => 0,
        'total_reviews' => 0,
    ];

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
    public string $postalCode = '';

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
        // Store only the ID, not the full model to avoid hydration issues
        $profile = FarmProfile::where('user_id', auth()->id())->first();

        if ($profile) {
            $this->profileModelId = $profile->id;
            $this->farmName = $profile->farm_name;
            $this->description = $profile->description ?? '';
            $this->phone = $profile->phone ?? '';
            $this->village = $profile->village ?? '';
            $this->district = $profile->district ?? '';
            $this->regency = $profile->regency ?? '';
            $this->province = $profile->province ?? '';
            $this->address = $profile->address ?? '';
            $this->postalCode = $profile->postal_code ?? '';
            $this->existingImage = $profile->image;
            $this->existingCoverImage = $profile->cover_image;
            $this->existingLogo = $profile->logo;
            $this->latitude = $profile->latitude;
            $this->longitude = $profile->longitude;
            $this->googlePlaceId = $profile->google_place_id ?? '';
            $this->galleries = $profile->galleries()->get()->toArray();

            // Extract profile data for blade template
            $this->profileCompletion = $profile->profile_completion ?? 0;
            $this->missingFields = $profile->missing_fields ?? [];
            $this->isVerified = $profile->is_verified ?? false;
            $this->stats = $profile->stats ?? [
                'active_products' => 0,
                'completed_bookings' => 0,
                'rating' => 0,
                'total_reviews' => 0,
            ];
        } else {
            // Reset values when no profile exists
            $this->profileCompletion = 0;
            $this->missingFields = [];
            $this->isVerified = false;
            $this->stats = [
                'active_products' => 0,
                'completed_bookings' => 0,
                'rating' => 0,
                'total_reviews' => 0,
            ];
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
                'postal_code' => $this->postalCode ?: null,
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
            FarmProfile::where('id', $this->profileModelId)->update(['cover_image' => null]);
            $this->existingCoverImage = null;
        } elseif ($type === 'logo' && $this->existingLogo) {
            Storage::disk('public')->delete($this->existingLogo);
            FarmProfile::where('id', $this->profileModelId)->update(['logo' => null]);
            $this->existingLogo = null;
        } elseif ($type === 'image' && $this->existingImage) {
            Storage::disk('public')->delete($this->existingImage);
            FarmProfile::where('id', $this->profileModelId)->update(['image' => null]);
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
        if ($gallery && $this->profileModelId && $gallery->farm_profile_id === $this->profileModelId) {
            Storage::disk('public')->delete($gallery->image);
            $gallery->delete();
            // Reload galleries from fresh query
            $this->galleries = FarmProfile::find($this->profileModelId)?->galleries()->get()->toArray() ?? [];
        }
    }

    public function render()
    {
        return view('livewire.peternak.farm-profile');
    }
}
