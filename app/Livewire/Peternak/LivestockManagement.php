<?php

namespace App\Livewire\Peternak;

use App\Models\Category;
use App\Models\FarmProfile;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.main')]
#[Title('Manajemen Ternak')]
class LivestockManagement extends Component
{
    use WithFileUploads;

    // Filters
    public string $searchQuery = '';
    public string $statusFilter = 'all';
    public string $categoryFilter = 'all';

    // Form Fields
    public ?int $editingId = null;
    public string $name = '';
    public string $categoryId = '';
    public string $breed = '';
    public string $age = '';
    public string $weight = '';
    public string $sex = 'male';
    public string $price = '';
    public string $description = '';
    public string $barnLocation = '';
    public string $origin = '';
    public string $condition = '';
    public string $status = 'available';
    public $image = null;
    public ?string $existingImage = null;
    public array $gallery = [];
    public array $newGalleryImages = [];

    // UI State
    public bool $showModal = false;
    public ?int $pendingDeleteId = null;
    public string $viewMode = 'grid'; // grid or table

    public function mount()
    {
        // Load farm profile for auto-sync
        $this->syncFromFarmProfile();
    }

    /**
     * Sync barn location and origin from farm profile
     * - Origin: farm_name
     * - Barn Location: address, fallback to village
     */
    protected function syncFromFarmProfile(): void
    {
        $farmProfile = FarmProfile::where('user_id', auth()->id())->first();

        if ($farmProfile) {
            // Origin = farm_name
            $this->origin = $farmProfile->farm_name ?? '';

            // Barn Location = address, fallback to village
            $this->barnLocation = $farmProfile->address
                ?: ($farmProfile->village
                    ? "{$farmProfile->village}" . ($farmProfile->district ? ", {$farmProfile->district}" : '')
                    : '');
        } else {
            $this->origin = '';
            $this->barnLocation = '';
        }
    }

    public function openModal(?int $id = null)
    {
        if ($id) {
            // Edit existing livestock - keep existing values
            $product = Product::where('owner_id', auth()->id())->findOrFail($id);
            $this->editingId = $product->id;
            $this->name = $product->name;
            $this->categoryId = (string) $product->category_id;
            $this->breed = $product->breed ?? '';
            $this->age = $product->age ?? '';
            $this->weight = $product->weight ? (string) $product->weight : '';
            $this->sex = $product->sex ?? 'male';
            $this->price = (string) $product->price;
            $this->description = $product->description ?? '';
            $this->barnLocation = $product->barn_location ?? '';
            $this->origin = $product->origin ?? '';
            $this->condition = $product->condition ?? '';
            $this->status = $product->status ?? 'available';
            $this->existingImage = $product->image;
            $this->gallery = $product->images ?? [];
        } else {
            // New livestock - sync from farm profile
            $this->resetForm();
            $this->syncFromFarmProfile();
        }
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'categoryId' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'sex' => 'required|in:male,female,mixed',
            'status' => 'required|in:available,reserved,sold,inactive',
        ]);

        try {
            $data = [
                'name' => $this->name,
                'category_id' => (int) $this->categoryId,
                'breed' => $this->breed ?: null,
                'age' => $this->age ?: null,
                'weight' => $this->weight ? (float) $this->weight : null,
                'sex' => $this->sex,
                'price' => (float) $this->price,
                'description' => $this->description ?: null,
                'barn_location' => $this->barnLocation ?: null,
                'origin' => $this->origin ?: null,
                'condition' => $this->condition ?: null,
                'status' => $this->status,
                'type' => 'live', // Peternak only manages live livestock
                'owner_id' => auth()->id(),
            ];

            // Handle main image
            if ($this->image) {
                if ($this->editingId && $this->existingImage) {
                    Storage::disk('public')->delete($this->existingImage);
                }
                $data['image'] = $this->image->store('livestock', 'public');
            }

            // Handle gallery images
            if ($this->editingId && empty($this->newGalleryImages)) {
                $data['images'] = $this->gallery;
            } elseif (!empty($this->newGalleryImages)) {
                $newImages = [];
                foreach ($this->newGalleryImages as $img) {
                    $newImages[] = $img->store('livestock/gallery', 'public');
                }
                $data['images'] = array_merge($this->gallery, $newImages);
            }

            if ($this->editingId) {
                Product::findOrFail($this->editingId)->update($data);
                session()->flash('success', 'Ternak berhasil diperbarui!');
            } else {
                Product::create($data);
                session()->flash('success', 'Ternak berhasil ditambahkan!');
            }

            $this->closeModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Error: ' . $e->getMessage());
        }
    }

    public function confirmDelete(int $id)
    {
        // Check if product has been sold
        $product = Product::where('owner_id', auth()->id())->findOrFail($id);
        if ($product->status === 'sold') {
            session()->flash('error', 'Ternak yang sudah terjual tidak dapat dihapus.');
            return;
        }
        $this->pendingDeleteId = $id;
    }

    public function deleteConfirmed()
    {
        if (!$this->pendingDeleteId) return;

        $product = Product::where('owner_id', auth()->id())->find($this->pendingDeleteId);
        if (!$product) {
            session()->flash('error', 'Ternak tidak ditemukan.');
            $this->pendingDeleteId = null;
            return;
        }

        if ($product->status === 'sold') {
            session()->flash('error', 'Ternak yang sudah terjual tidak dapat dihapus.');
            $this->pendingDeleteId = null;
            return;
        }

        try {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            if ($product->images) {
                foreach ($product->images as $img) {
                    Storage::disk('public')->delete($img);
                }
            }
            $product->delete();
            session()->flash('success', 'Ternak berhasil dihapus!');
        } catch (\Exception $e) {
            session()->flash('error', 'Error: ' . $e->getMessage());
        }

        $this->pendingDeleteId = null;
    }

    private function resetForm()
    {
        $this->editingId = null;
        $this->name = '';
        $this->categoryId = '';
        $this->breed = '';
        $this->age = '';
        $this->weight = '';
        $this->sex = 'male';
        $this->price = '';
        $this->description = '';
        $this->barnLocation = '';
        $this->origin = '';
        $this->condition = '';
        $this->status = 'available';
        $this->image = null;
        $this->existingImage = null;
        $this->gallery = [];
        $this->newGalleryImages = [];
    }

    public function removeGalleryImage(int $index)
    {
        if (isset($this->gallery[$index])) {
            $img = $this->gallery[$index];
            Storage::disk('public')->delete($img);
            array_splice($this->gallery, $index, 1);
        }
    }

    public function render()
    {
        $userId = auth()->id();

        $livestocksQuery = Product::where('owner_id', $userId)
            ->where('type', 'live')
            ->when($this->searchQuery, function ($q) {
                $q->where(function ($query) {
                    $query->where('name', 'like', '%' . $this->searchQuery . '%')
                        ->orWhere('breed', 'like', '%' . $this->searchQuery . '%');
                });
            })
            ->when($this->statusFilter !== 'all', function ($q) {
                $q->where('status', $this->statusFilter);
            })
            ->when($this->categoryFilter !== 'all', function ($q) {
                $q->where('category_id', $this->categoryFilter);
            })
            ->with('category')
            ->orderBy('created_at', 'desc');

        $livestocks = $livestocksQuery->get();
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        // Stats
        $stats = [
            'total' => Product::where('owner_id', $userId)->where('type', 'live')->count(),
            'available' => Product::where('owner_id', $userId)->where('type', 'live')->where('status', 'available')->count(),
            'reserved' => Product::where('owner_id', $userId)->where('type', 'live')->where('status', 'reserved')->count(),
            'sold' => Product::where('owner_id', $userId)->where('type', 'live')->where('status', 'sold')->count(),
        ];

        return view('livewire.peternak.livestock-management', [
            'livestocks' => $livestocks,
            'categories' => $categories,
            'stats' => $stats,
        ]);
    }
}
