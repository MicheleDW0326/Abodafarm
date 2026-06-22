<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use App\Models\InventoryLog;
use App\Models\Product;
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
    public string $typeFilter = 'all'; // all, live, fresh

    // Product Form
    public ?int $editingProductId = null;
    public string $productName = '';
    public string $productDescription = '';
    public string $productPrice = '';
    public string $productCategory = '';
    public string $productType = 'fresh';
    public string $productStatus = 'available';
    public string $productWeight = '';
    public string $productAge = '';
    public string $productSex = '';
    public string $productOrigin = '';
    public string $productCondition = '';
    public string $productWeightPerUnit = '';
    public int $productStock = 0;
    public bool $productIsFeatured = false;
    public $productImage = null;
    public ?string $productImagePath = null;

    // Category Form
    public ?int $editingCategoryId = null;
    public string $categoryName = '';
    public string $categoryDescription = '';
    public string $categorySlug = '';
    public string $categoryIcon = '';

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
            $product = Product::findOrFail($productId);
            $this->editingProductId = $product->id;
            $this->productName = $product->name;
            $this->productDescription = $product->description ?? '';
            $this->productPrice = (string) $product->price;
            $this->productCategory = (string) $product->category_id;
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
        } else {
            $this->resetProductForm();
        }
        $this->showProductModal = true;
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
            'productType' => 'required|in:live,fresh',
            'productStatus' => 'required|in:available,pre_order,in_care,sold',
        ]);

        try {
            $data = [
                'name' => $this->productName,
                'description' => $this->productDescription,
                'price' => (float) $this->productPrice,
                'category_id' => (int) $this->productCategory,
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
            'confirmAction' => 'deleteProductConfirmed'
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
                'description' => $this->categoryDescription,
                'slug' => $this->categorySlug ?: \Illuminate\Support\Str::slug($this->categoryName),
                'icon' => $this->categoryIcon ?: null,
                'is_active' => true,
            ];

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
        $category = Category::find($categoryId);
        if ($category && $category->products()->count() > 0) {
            $this->dispatch('showAlert', ['type' => 'error', 'title' => 'Gagal!', 'message' => 'Tidak dapat menghapus kategori yang memiliki produk!']);
            return;
        }
        $this->pendingDeleteCategoryId = $categoryId;
        $this->dispatch('showConfirm', [
            'title' => 'Konfirmasi Hapus',
            'message' => 'Apakah Anda yakin ingin menghapus kategori ini?',
            'confirmText' => 'Ya, Hapus',
            'confirmAction' => 'deleteCategoryConfirmed'
        ]);
    }

    public function deleteCategoryConfirmed()
    {
        if (!$this->pendingDeleteCategoryId) {
            return;
        }

        try {
            Category::findOrFail($this->pendingDeleteCategoryId)->delete();
            $this->dispatch('showAlert', ['type' => 'success', 'title' => 'Berhasil!', 'message' => 'Kategori berhasil dihapus!']);
        } catch (\Exception $e) {
            $this->dispatch('showAlert', ['type' => 'error', 'title' => 'Error!', 'message' => $e->getMessage()]);
        }

        $this->pendingDeleteCategoryId = null;
    }

    private function resetCategoryForm()
    {
        $this->editingCategoryId = null;
        $this->categoryName = '';
        $this->categoryDescription = '';
        $this->categorySlug = '';
        $this->categoryIcon = '';
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
        $productsQuery = Product::with('category')
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

        // Categories
        $categories = Category::ordered()->get();

        // Inventory Logs
        $inventoryLogs = InventoryLog::with('product')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return view('livewire.admin.product-management', [
            'products' => $products,
            'liveProductsCount' => $liveProducts,
            'freshProductsCount' => $freshProducts,
            'categories' => $categories,
            'inventoryLogs' => $inventoryLogs,
        ]);
    }
}
