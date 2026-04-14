<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductStock;
use App\Traits\WithNotifications;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.main')]
#[Title('Menejemen Produk')]
class ProductManagement extends Component
{
    use WithPagination, WithFileUploads, WithNotifications;

    public string $activeTab = 'products';
    public string $searchQuery = '';

    // Product Form
    public ?int $editingProductId = null;
    public ?string $productImagePath = null;
    #[Validate('required|string|max:255')]
    public string $productName = '';
    #[Validate('required|numeric|min:0')]
    public string $productPrice = '';
    #[Validate('nullable|string|max:1000')]
    public string $productDescription = '';
    #[Validate('required|exists:categories,id')]
    public string $productCategory = '';
    #[Validate('nullable|image|max:2048')]
    public $productImage = null;

    // Stock Form
    public ?int $editingStockProductId = null;
    #[Validate('required|numeric|min:1')]
    public int $stockQuantity = 0;
    #[Validate('required|string|max:255')]
    public string $stockSupplier = '';
    #[Validate('required|date')]
    public string $stockDate = '';

    // UI
    public bool $showProductModal = false;
    public bool $showStockModal = false;
    public ?int $pendingDeleteProductId = null;

    public function mount()
    {
        if (request()->has('tab')) {
            $this->activeTab = request()->query('tab');
        }
        $this->stockDate = now()->toDateString();
    }

    public function setTab(string $tab)
    {
        $this->activeTab = $tab;
    }

    // Product CRUD Methods
    public function openProductModal(?int $productId = null)
    {
        if ($productId) {
            $product = Product::find($productId, ['*']);

            if (!$product) {
                $this->alertError('Produk tidak ditemukan.');
                return;
            }

            $this->editingProductId = $product->id;
            $this->productName = $product->name;
            $this->productPrice = (string) $product->price;
            $this->productDescription = $product->description;
            $this->productCategory = (string) $product->category_id;
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
        $this->validate();

        try {
            $data = [
                'name' => $this->productName,
                'price' => (float) $this->productPrice,
                'description' => $this->productDescription,
                'category_id' => (int) $this->productCategory,
            ];

            if ($this->productImage) {
                if ($this->editingProductId && $this->productImagePath && Storage::disk('public')->exists($this->productImagePath)) {
                    Storage::disk('public')->delete($this->productImagePath);
                }
                $data['image'] = $this->productImage->store('products', 'public');
            }

            if ($this->editingProductId) {
                Product::findOrFail($this->editingProductId)->update($data);
                $this->alertSuccess('Produk berhasil diperbarui!');
            } else {
                $data['stock'] = 0;
                Product::create($data);
                $this->alertSuccess('Produk berhasil ditambahkan!');
            }

            $this->closeProductModal();
        } catch (\Exception $e) {
            $this->alertError('Error: ' . $e->getMessage());
        }
    }

    public function confirmDeleteProduct(int $productId)
    {
        $this->pendingDeleteProductId = $productId;
        $this->confirmDelete('Hapus produk?', 'Apakah Anda yakin ingin menghapus produk ini?', 'deleteProductConfirmed');
    }

    public function deleteProductConfirmed()
    {
        if (!$this->pendingDeleteProductId) {
            return;
        }

        $product = Product::find($this->pendingDeleteProductId, ['*']);

        if (!$product) {
            $this->alertError('Produk tidak ditemukan.');
            $this->pendingDeleteProductId = null;
            return;
        }

        try {
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
            Product::query()->whereKey($product->id)->delete();
            $this->alertSuccess('Produk berhasil dihapus!');
        } catch (\Exception $e) {
            $this->alertError('Error: ' . $e->getMessage());
        } finally {
            $this->pendingDeleteProductId = null;
        }
    }

    private function resetProductForm()
    {
        $this->editingProductId = null;
        $this->productImagePath = null;
        $this->productName = '';
        $this->productPrice = '';
        $this->productDescription = '';
        $this->productCategory = '';
        $this->productImage = null;
    }

    // Stock CRUD Methods
    public function openStockModal(int $productId)
    {
        if (!$productId) {
            $this->alertError('Pilih produk dulu!');
            return;
        }
        $this->editingStockProductId = $productId;
        $this->showStockModal = true;
    }

    public function closeStockModal()
    {
        $this->showStockModal = false;
        $this->resetStockForm();
    }

    public function saveStock()
    {
        $this->validate([
            'stockQuantity' => 'required|numeric|min:1',
            'stockSupplier' => 'required|string|max:255',
            'stockDate' => 'required|date',
        ]);

        try {
            $product = Product::findOrFail($this->editingStockProductId);

            ProductStock::create([
                'product_id' => $product->id,
                'quantity' => $this->stockQuantity,
                'supplier' => $this->stockSupplier,
                'date' => $this->stockDate,
            ]);

            // Update product stock
            $product->increment('stock', $this->stockQuantity);

            $this->alertSuccess('Stok berhasil ditambahkan!');
            $this->closeStockModal();
        } catch (\Exception $e) {
            $this->alertError('Error: ' . $e->getMessage());
        }
    }

    private function resetStockForm()
    {
        $this->editingStockProductId = null;
        $this->stockQuantity = 0;
        $this->stockSupplier = '';
        $this->stockDate = now()->toDateString();
    }

    #[\Livewire\Attributes\Computed]
    public function editingStockProduct()
    {
        if ($this->editingStockProductId) {
            return Product::findOrFail($this->editingStockProductId);
        }
        return null;
    }

    public function render()
    {
        $products = Product::with('category')
            ->when($this->searchQuery, function ($query) {
                $query->where('name', 'like', '%' . $this->searchQuery . '%')
                    ->orWhere('description', 'like', '%' . $this->searchQuery . '%');
            })
            ->get();

        $categories = Category::all();
        $stocks = ProductStock::with('product')->orderBy('date', 'desc')->get();

        return view('livewire.admin.product-management', [
            'products' => $products,
            'categories' => $categories,
            'stocks' => $stocks,
        ]);
    }
}

