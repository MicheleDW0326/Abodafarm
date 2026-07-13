<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use App\Models\FarmProfile;
use App\Models\InventoryLog;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.main')]
#[Title('Manajemen Produk')]
class ProductManagement extends Component
{
    use WithPagination, WithFileUploads;

    // Tabs
    public string $activeTab = 'products';

    // Filters
    public string $searchQuery = '';
    public string $typeFilter = 'all'; // all, live, fresh, supply

    // Product Form
    public ?int $editingProductId = null;
    public string $productName = '';
    public string $productDescription = '';
    public string $productPrice = '';
    public string $productCategory = '';
    public string $productOwner = ''; // owner_id (peternak)
    public string $productType = 'fresh';
    public string $productStatus = 'available';
    public string $productWeight = '';
    public string $productAge = '';
    public string $productSex = '';
    public string $productOrigin = '';
    public string $productCondition = '';
    public string $productBarnLocation = ''; // Auto-filled from farmer's farm
    public string $productWeightPerUnit = '';
    public int $productStock = 0;
    public bool $productIsFeatured = false;
    public $productImage = null;
    public ?string $productImagePath = null;

    // Supply product fields
    public string $productDiscountPrice = '';
    public string $productDiscountPercent = '';
    public string $productSpecifications = '';
    public string $productHowToUse = '';
    public string $productShippingInfo = '';
    public string $productUnit = '';

    // Category Form
    public ?int $editingCategoryId = null;
    public string $categoryName = '';
    public string $categoryDescription = '';
    public string $categorySlug = '';
    public string $categoryIcon = '';
    public $categoryImage = null;
    public ?string $categoryImagePath = null;
    public bool $categoryIsActive = true;
    public int $categorySortOrder = 0;

    // Inventory Form
    public ?int $editingInventoryProductId = null;
    public string $inventoryQuantity = '';
    public string $inventoryType = 'restock';
    public string $inventoryHealthStatus = '';
    public string $inventoryVaccinationRecords = '';
    public string $inventoryNotes = '';
    public string $inventoryDate = '';

    // UI State
    public bool $showProductModal = false;
    public bool $showCategoryModal = false;
    public bool $showInventoryModal = false;
    public ?int $pendingDeleteProductId = null;
    public ?int $pendingDeleteCategoryId = null;

    // Care Record Form (for live products)
    public ?int $editingCareProductId = null;
    public string $careType = 'feeding';
    public string $careDate = '';
    public string $careNotes = '';

    public function mount()
    {
        if (request()->has('tab')) {
            $this->activeTab = request()->query('tab');
        }
        $this->inventoryDate = now()->toDateString();
        $this->careDate = now()->toDateString();
    }

    // ==================== TABS & FILTERS ====================

    public function setTab(string $tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function updatedTypeFilter()
    {
        $this->resetPage();
    }

    // ==================== PRODUCT CRUD ====================

    public function openProductModal(?int $productId = null)
    {
        if ($productId) {
            $product = Product::with('owner.farmProfile')->findOrFail($productId);
            $this->editingProductId = $product->id;
            $this->productName = $product->name;
            $this->productDescription = $product->description ?? '';
            $this->productPrice = (string) $product->price;
            $this->productCategory = (string) $product->category_id;
            $this->productOwner = (string) $product->owner_id ?? '';
            $this->productBarnLocation = $product->barn_location ?? '';
            $this->productType = $product->type ?? 'fresh';
            $this->productStatus = $product->status ?? 'available';
            $this->productWeight = $product->weight ? (string) $product->weight : '';
            $this->productAge = $product->age ?? '';
            $this->productSex = $product->sex ?? '';
            $this->productOrigin = $product->origin ?? '';
            $this->productCondition = $product->condition ?? '';
            $this->productWeightPerUnit = $product->weight_per_unit ? (string) $product->weight_per_unit : '';
            $this->productStock = $product->stock ?? 0;
            $this->productIsFeatured = $product->is_featured ?? false;
            $this->productImagePath = $product->image;
            // Supply-specific fields
            $this->productDiscountPrice = $product->discount_price ? (string) $product->discount_price : '';
            $this->productDiscountPercent = $product->discount_percent ? (string) $product->discount_percent : '';
            $this->productSpecifications = $product->specifications ?? '';
            $this->productHowToUse = $product->how_to_use ?? '';
            $this->productShippingInfo = $product->shipping_info ?? '';
            $this->productUnit = $product->unit ?? '';
        } else {
            $this->resetProductForm();
        }
        $this->showProductModal = true;
    }

    public function updatedProductOwner(string $ownerId)
    {
        // Auto-fill barn location and origin from farmer's farm profile
        // - Origin: farm_name
        // - Barn Location: address, fallback to village
        if ($ownerId) {
            $farmProfile = FarmProfile::where('user_id', $ownerId)->first();
            if ($farmProfile) {
                // Origin = farm_name
                $this->productOrigin = $farmProfile->farm_name ?? '';

                // Barn Location = address, fallback to village
                $this->productBarnLocation = $farmProfile->address
                    ?: ($farmProfile->village
                        ? "{$farmProfile->village}" . ($farmProfile->district ? ", {$farmProfile->district}" : '')
                        : '');
            }
        } else {
            // Clear when no owner selected
            $this->productBarnLocation = '';
            $this->productOrigin = '';
        }
    }

    public function closeProductModal()
    {
        $this->showProductModal = false;
        $this->resetProductForm();
    }

    public function saveProduct()
    {
        $this->validate([
            'productName' => 'required|string|max:255',
            'productPrice' => 'required|numeric|min:0',
            'productCategory' => 'required|exists:categories,id',
            'productType' => 'required|in:live,fresh,supply',
            'productStatus' => 'required|in:available,pre_order,in_care,sold',
            'productOwner' => 'nullable|exists:users,id',
        ]);

        try {
            $data = [
                'name' => $this->productName,
                'description' => $this->productDescription,
                'price' => (float) $this->productPrice,
                'category_id' => (int) $this->productCategory,
                'owner_id' => $this->productOwner ? (int) $this->productOwner : null,
                'barn_location' => $this->productBarnLocation ?: null,
                'type' => $this->productType,
                'status' => $this->productStatus,
                'weight' => $this->productWeight ? (float) $this->productWeight : null,
                'age' => $this->productAge ?: null,
                'sex' => $this->productSex ?: null,
                'origin' => $this->productOrigin ?: null,
                'condition' => $this->productCondition ?: null,
                'weight_per_unit' => $this->productWeightPerUnit ? (float) $this->productWeightPerUnit : null,
                'stock' => $this->productStock,
                'is_featured' => $this->productIsFeatured,
                // Supply-specific fields
                'discount_price' => $this->productDiscountPrice ? (float) $this->productDiscountPrice : null,
                'discount_percent' => $this->productDiscountPercent ? (float) $this->productDiscountPercent : null,
                'specifications' => $this->productSpecifications ?: null,
                'how_to_use' => $this->productHowToUse ?: null,
                'shipping_info' => $this->productShippingInfo ?: null,
                'unit' => $this->productUnit ?: null,
            ];

            if ($this->productImage) {
                if ($this->editingProductId && $this->productImagePath && Storage::disk('public')->exists($this->productImagePath)) {
                    Storage::disk('public')->delete($this->productImagePath);
                }
                $data['image'] = $this->productImage->store('products', 'public');
            }

            if ($this->editingProductId) {
                Product::findOrFail($this->editingProductId)->update($data);
                $this->dispatch('showAlert', ['type' => 'success', 'title' => 'Berhasil!', 'message' => 'Produk berhasil diperbarui!']);
            } else {
                Product::create($data);
                $this->dispatch('showAlert', ['type' => 'success', 'title' => 'Berhasil!', 'message' => 'Produk berhasil ditambahkan!']);
            }

            $this->closeProductModal();
        } catch (\Exception $e) {
            $this->dispatch('showAlert', ['type' => 'error', 'title' => 'Error!', 'message' => $e->getMessage()]);
        }
    }

    public function confirmDeleteProduct(int $productId)
    {
        $this->pendingDeleteProductId = $productId;
        $this->dispatch('showConfirm', [
            'title' => 'Konfirmasi Hapus',
            'message' => 'Apakah Anda yakin ingin menghapus produk ini?',
            'confirmText' => 'Ya, Hapus',
            'confirmAction' => 'deleteProductConfirmed',
            'componentId' => $this->id(),
        ]);
    }

    public function deleteProductConfirmed()
    {
        if (!$this->pendingDeleteProductId) {
            return;
        }

        $product = Product::find($this->pendingDeleteProductId);

        if (!$product) {
            $this->dispatch('showAlert', ['type' => 'error', 'title' => 'Error!', 'message' => 'Produk tidak ditemukan.']);
            $this->pendingDeleteProductId = null;
            return;
        }

        try {
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
            $product->delete();
            $this->dispatch('showAlert', ['type' => 'success', 'title' => 'Berhasil!', 'message' => 'Produk berhasil dihapus!']);
        } catch (\Exception $e) {
            $this->dispatch('showAlert', ['type' => 'error', 'title' => 'Error!', 'message' => $e->getMessage()]);
        }

        $this->pendingDeleteProductId = null;
    }

    private function resetProductForm()
    {
        $this->editingProductId = null;
        $this->productName = '';
        $this->productDescription = '';
        $this->productPrice = '';
        $this->productCategory = '';
        $this->productOwner = '';
        $this->productBarnLocation = '';
        $this->productType = 'fresh';
        $this->productStatus = 'available';
        $this->productWeight = '';
        $this->productAge = '';
        $this->productSex = '';
        $this->productOrigin = '';
        $this->productCondition = '';
        $this->productWeightPerUnit = '';
        $this->productStock = 0;
        $this->productIsFeatured = false;
        $this->productImage = null;
        $this->productImagePath = null;
        // Supply-specific fields
        $this->productDiscountPrice = '';
        $this->productDiscountPercent = '';
        $this->productSpecifications = '';
        $this->productHowToUse = '';
        $this->productShippingInfo = '';
        $this->productUnit = '';
    }

    // ==================== CATEGORY CRUD ====================

    public function openCategoryModal(?int $categoryId = null)
    {
        if ($categoryId) {
            $category = Category::findOrFail($categoryId);
            $this->editingCategoryId = $category->id;
            $this->categoryName = $category->name;
            $this->categoryDescription = $category->description ?? '';
            $this->categorySlug = $category->slug ?? '';
            $this->categoryIcon = $category->icon ?? '';
            $this->categoryImagePath = $category->image;
            $this->categoryIsActive = $category->is_active;
            $this->categorySortOrder = $category->sort_order;
        } else {
            $this->resetCategoryForm();
        }
        $this->showCategoryModal = true;
    }

    public function closeCategoryModal()
    {
        $this->showCategoryModal = false;
        $this->resetCategoryForm();
    }

    public function saveCategory()
    {
        $this->validate([
            'categoryName' => 'required|string|max:255',
            'categorySlug' => 'nullable|string|max:255|unique:categories,slug,' . ($this->editingCategoryId ?? 'NULL') . ',id',
        ]);

        try {
            $data = [
                'name' => $this->categoryName,
                'description' => $this->categoryDescription ?: null,
                'slug' => $this->categorySlug ?: \Illuminate\Support\Str::slug($this->categoryName),
                'icon' => $this->categoryIcon ?: null,
                'is_active' => $this->categoryIsActive,
                'sort_order' => $this->categorySortOrder,
            ];

            if ($this->categoryImage) {
                if ($this->editingCategoryId && $this->categoryImagePath && Storage::disk('public')->exists($this->categoryImagePath)) {
                    Storage::disk('public')->delete($this->categoryImagePath);
                }
                $data['image'] = $this->categoryImage->store('categories', 'public');
            }

            if ($this->editingCategoryId) {
                Category::findOrFail($this->editingCategoryId)->update($data);
                $this->dispatch('showAlert', ['type' => 'success', 'title' => 'Berhasil!', 'message' => 'Kategori berhasil diperbarui!']);
            } else {
                Category::create($data);
                $this->dispatch('showAlert', ['type' => 'success', 'title' => 'Berhasil!', 'message' => 'Kategori berhasil ditambahkan!']);
            }

            $this->closeCategoryModal();
        } catch (\Exception $e) {
            $this->dispatch('showAlert', ['type' => 'error', 'title' => 'Error!', 'message' => $e->getMessage()]);
        }
    }

    public function confirmDeleteCategory(int $categoryId)
    {
        $this->pendingDeleteCategoryId = $categoryId;
        $this->dispatch('showConfirm', [
            'title' => 'Konfirmasi Hapus',
            'message' => 'Apakah Anda yakin ingin menghapus kategori ini?',
            'confirmText' => 'Ya, Hapus',
            'confirmAction' => 'deleteCategoryConfirmed',
            'componentId' => $this->id(),
        ]);
    }

    public function deleteCategoryConfirmed()
    {
        if (!$this->pendingDeleteCategoryId) {
            return;
        }

        try {
            $category = Category::findOrFail($this->pendingDeleteCategoryId);
            if ($category->image && !str_starts_with($category->image, 'http')) {
                Storage::disk('public')->delete($category->image);
            }
            $category->delete();
            $this->dispatch('showAlert', ['type' => 'success', 'title' => 'Berhasil!', 'message' => 'Kategori berhasil dihapus!']);
        } catch (\Exception $e) {
            $this->dispatch('showAlert', ['type' => 'error', 'title' => 'Error!', 'message' => $e->getMessage()]);
        }

        $this->pendingDeleteCategoryId = null;
    }

    public function toggleCategoryActive(int $categoryId)
    {
        $category = Category::findOrFail($categoryId);
        $category->update(['is_active' => !$category->is_active]);
        $this->dispatch('showAlert', [
            'type' => 'success',
            'title' => 'Berhasil!',
            'message' => 'Status kategori berhasil diperbarui!'
        ]);
    }

    private function resetCategoryForm()
    {
        $this->editingCategoryId = null;
        $this->categoryName = '';
        $this->categoryDescription = '';
        $this->categorySlug = '';
        $this->categoryIcon = '';
        $this->categoryImage = null;
        $this->categoryImagePath = null;
        $this->categoryIsActive = true;
        $this->categorySortOrder = 0;
    }

    // ==================== INVENTORY CRUD ====================

    public function openInventoryModal(int $productId)
    {
        $this->editingInventoryProductId = $productId;
        $this->resetInventoryForm();
        $this->showInventoryModal = true;
    }

    public function closeInventoryModal()
    {
        $this->showInventoryModal = false;
        $this->resetInventoryForm();
    }

    public function saveInventory()
    {
        $this->validate([
            'inventoryQuantity' => 'required|numeric|min:1',
            'inventoryType' => 'required|in:restock,sale,death,transfer,adjustment',
            'inventoryDate' => 'required|date',
        ]);

        try {
            $product = Product::findOrFail($this->editingInventoryProductId);

            // Create inventory log
            InventoryLog::create([
                'product_id' => $product->id,
                'quantity' => (int) $this->inventoryQuantity,
                'type' => $this->inventoryType,
                'health_status' => $this->inventoryHealthStatus ?: null,
                'vaccination_records' => $this->inventoryVaccinationRecords ?: null,
                'notes' => $this->inventoryNotes ?: null,
                'date' => $this->inventoryDate,
                'recorded_by' => auth()->id(),
            ]);

            // Update product stock based on type
            if (in_array($this->inventoryType, ['restock', 'adjustment'])) {
                $product->increment('stock', (int) $this->inventoryQuantity);
            } elseif (in_array($this->inventoryType, ['sale', 'death', 'transfer'])) {
                $product->decrement('stock', (int) $this->inventoryQuantity);
            }

            $this->dispatch('showAlert', ['type' => 'success', 'title' => 'Berhasil!', 'message' => 'Inventaris berhasil dicatat!']);
            $this->closeInventoryModal();
        } catch (\Exception $e) {
            $this->dispatch('showAlert', ['type' => 'error', 'title' => 'Error!', 'message' => $e->getMessage()]);
        }
    }

    private function resetInventoryForm()
    {
        $this->inventoryQuantity = '';
        $this->inventoryType = 'restock';
        $this->inventoryHealthStatus = '';
        $this->inventoryVaccinationRecords = '';
        $this->inventoryNotes = '';
        $this->inventoryDate = now()->toDateString();
    }

    // ==================== RENDER ====================

    public function render()
    {
        // Products Query
        $productsQuery = Product::with(['category', 'owner.farmProfile'])
            ->when($this->searchQuery, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->searchQuery . '%')
                        ->orWhere('description', 'like', '%' . $this->searchQuery . '%');
                });
            })
            ->when($this->typeFilter !== 'all', function ($query) {
                $query->where('type', $this->typeFilter);
            })
            ->orderBy('created_at', 'desc');

        $products = $productsQuery->get();
        $liveProducts = Product::live()->count();
        $freshProducts = Product::freshType()->count();
        $supplyProducts = Product::supply()->count();

        // Categories
        $categories = Category::ordered()->get();

        // Peternak (Users with peternak role)
        $peternaks = User::role('peternak')
            ->with('farmProfile')
            ->orderBy('name')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'farm_name' => $user->farmProfile?->farm_name ?? 'Belum ada profil',
                ];
            });

        // Inventory Logs
        $inventoryLogs = InventoryLog::with('product')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return view('livewire.admin.product-management', [
            'products' => $products,
            'liveProductsCount' => $liveProducts,
            'freshProductsCount' => $freshProducts,
            'supplyProductsCount' => $supplyProducts,
            'categories' => $categories,
            'peternaks' => $peternaks,
            'inventoryLogs' => $inventoryLogs,
        ]);
    }
}
