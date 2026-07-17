<?php

namespace App\Livewire\Admin;

use App\Models\Product;
use App\Services\ProductImportService;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.main')]
#[Title('Import Produk')]
class ImportProducts extends Component
{
    use WithFileUploads;

    public $file = null;

    public string $step = 'upload';

    public array $validRows = [];
    public array $invalidRows = [];
    public array $validationErrors = [];
    public int $totalRows = 0;
    public int $validCount = 0;
    public int $invalidCount = 0;
    public int $importedCount = 0;
    public int $skippedCount = 0;

    public int $progress = 0;
    public string $progressMessage = '';

    public bool $hasError = false;
    public string $errorMessage = '';

    public function updatedFile($value)
    {
        $this->resetValidation();
        $this->reset(['validRows', 'invalidRows', 'validationErrors', 'hasError', 'errorMessage']);
    }

    public function processFile()
    {
        $this->validate([
            'file' => 'required|file|mimes:csv,xls,xlsx|max:10240', // max 10MB
        ]);

        try {
            $this->step = 'preview';
            $this->hasError = false;
            $this->errorMessage = '';

            $importService = new ProductImportService();
            $importService->processFile($this->file);

            // Store temporary data for confirmation
            $this->validRows = $importService->validRows;
            $this->invalidRows = $importService->invalidRows;
            $this->totalRows = $importService->totalRows;
            $this->validCount = $importService->validCount;
            $this->invalidCount = $importService->invalidCount;

            // Store the import service in session for later use
            session(['product_import_data' => [
                'validRows' => $this->validRows,
                'invalidRows' => $this->invalidRows,
                'totalRows' => $this->totalRows,
                'validCount' => $this->validCount,
                'invalidCount' => $this->invalidCount,
            ]]);

        } catch (\Exception $e) {
            $this->hasError = true;
            $this->errorMessage = 'Gagal membaca file: ' . $e->getMessage();
            $this->step = 'upload';
        }
    }

    public function confirmImport()
    {
        $this->step = 'importing';
        $this->progress = 0;
        $this->progressMessage = 'Memulai import...';

        try {
            $importService = new ProductImportService();
            $importService->validRows = $this->validRows;

            $total = count($this->validRows);
            $imported = 0;

            foreach ($this->validRows as $index => $row) {
                $data = $row['data'];

                $product = Product::create([
                    'name' => $data['name'],
                    'slug' => \Illuminate\Support\Str::slug($data['name']),
                    'category_id' => $data['category_id'],
                    'type' => $data['type'],
                    'status' => 'available',
                    'price' => $data['price'],
                    'stock' => $data['stock'],
                    'description' => $data['description'] ?: null,
                    'breed' => $data['breed'] ?: null,
                    'weight' => $data['weight'],
                    'age' => $data['age'] ?: null,
                    'sex' => $data['sex'],
                    'origin' => $data['origin'] ?: null,
                    'condition' => $data['condition'] ?: null,
                    'barn_location' => $data['barn_location'] ?: null,
                    'weight_per_unit' => $data['weight_per_unit'],
                    'unit' => $data['unit'] ?: null,
                    'discount_price' => $data['discount_price'],
                    'discount_percent' => $data['discount_percent'],
                    'specifications' => $data['specifications'] ?: null,
                    'how_to_use' => $data['how_to_use'] ?: null,
                    'shipping_info' => $data['shipping_info'] ?: null,
                    'is_featured' => $data['is_featured'],
                    'owner_id' => auth()->id(),
                ]);

                $imported++;

                // Update progress
                $this->progress = (int) (($imported / $total) * 100);
                $this->progressMessage = "Mengimport {$imported} dari {$total} produk...";
            }

            $this->importedCount = $imported;
            $this->skippedCount = 0;
            $this->step = 'complete';
            $this->progress = 100;
            $this->progressMessage = 'Import selesai!';

            // Clear session
            session()->forget('product_import_data');

            // Delete uploaded file
            if ($this->file) {
                Storage::delete('livewire-tmp/' . basename($this->file->getRealPath()));
            }

        } catch (\Exception $e) {
            $this->hasError = true;
            $this->errorMessage = 'Gagal mengimport: ' . $e->getMessage();
            $this->step = 'complete';
        }
    }

    public function cancelImport()
    {
        $this->reset();
        $this->step = 'upload';
        session()->forget('product_import_data');
    }

    public function resetForm()
    {
        $this->reset([
            'file', 'step', 'validRows', 'invalidRows', 'validationErrors',
            'totalRows', 'validCount', 'invalidCount', 'importedCount',
            'skippedCount', 'progress', 'progressMessage', 'hasError', 'errorMessage'
        ]);
        $this->step = 'upload';
    }

    public function render()
    {
        return view('livewire.admin.import-products');
    }
}