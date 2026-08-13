<?php

namespace App\Livewire\Admin;

use App\Models\Promotion;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.main')]
#[Title('Kelola Promo - AbodaFarm')]
class PromotionManagement extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public string $search = '';

    public bool $showModal = false;

    public bool $isEditing = false;

    // Form fields
    public ?int $editingId = null;

    public string $code = '';

    public ?string $title = '';

    public ?string $description = '';

    public string $type = 'percentage';

    public ?string $discountValue = '';

    public ?string $maxDiscountAmount = '';

    public ?string $minPurchaseAmount = '';

    public ?string $usageLimit = '';

    public string $perUserLimit = '1';

    public bool $isActive = true;

    public ?string $startsAt = null;

    public ?string $expiresAt = null;

    public function mount(): void
    {
        // Authorization handled by middleware (check.admin)
    }

    public function openModal(?int $id = null): void
    {
        $this->resetForm();

        if ($id) {
            $promotion = Promotion::find($id);

            if (! $promotion) {
                $this->dispatch('showAlert', ['type' => 'error', 'message' => 'Promo yang dipilih tidak ditemukan.']);

                return;
            }

            $this->isEditing = true;
            $this->editingId = $id;

            $this->code = $promotion->code ?? '';
            $this->title = $promotion->title ?? '';
            $this->description = $promotion->description ?? '';
            $this->type = $promotion->type ?? 'percentage';
            $this->discountValue = $promotion->discount_value !== null ? (string) $promotion->discount_value : '';
            $this->maxDiscountAmount = $promotion->max_discount_amount !== null ? (string) $promotion->max_discount_amount : '';
            $this->minPurchaseAmount = $promotion->min_purchase_amount !== null ? (string) $promotion->min_purchase_amount : '0';
            $this->usageLimit = $promotion->usage_limit !== null ? (string) $promotion->usage_limit : '';
            $this->perUserLimit = (string) ($promotion->per_user_limit ?? 1);
            $this->isActive = (bool) $promotion->is_active;
            $this->startsAt = $promotion->starts_at?->format('Y-m-d\TH:i');
            $this->expiresAt = $promotion->expires_at?->format('Y-m-d\TH:i');
        } else {
            $this->isEditing = false;
        }

        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->code = '';
        $this->title = '';
        $this->description = '';
        $this->type = 'percentage';
        $this->discountValue = '';
        $this->maxDiscountAmount = '';
        $this->minPurchaseAmount = '0';
        $this->usageLimit = '';
        $this->perUserLimit = '1';
        $this->isActive = true;
        $this->startsAt = null;
        $this->expiresAt = null;
        $this->isEditing = false;
    }

    public function save(): void
    {
        try {
            $this->validate([
                'code' => 'required|string|max:50|unique:promotions,code' . ($this->editingId ? ',' . $this->editingId : ''),
                'title' => 'required|string|max:200',
                'description' => 'nullable|string',
                'type' => 'required|in:percentage,fixed',
                'discountValue' => 'required|numeric|min:0',
                'maxDiscountAmount' => 'nullable|numeric|min:0',
                'minPurchaseAmount' => 'nullable|numeric|min:0',
                'usageLimit' => 'nullable|integer|min:1',
                'perUserLimit' => 'required|integer|min:1',
                'startsAt' => 'nullable|date',
                'expiresAt' => 'nullable|date|after_or_equal:startsAt',
            ]);

            $data = [
                'code' => strtoupper(trim($this->code)),
                'title' => trim($this->title),
                'description' => $this->description ? trim($this->description) : null,
                'type' => $this->type,
                'discount_value' => (float) $this->discountValue,
                'max_discount_amount' => $this->maxDiscountAmount ? (float) $this->maxDiscountAmount : null,
                'min_purchase_amount' => (float) ($this->minPurchaseAmount ?: 0),
                'usage_limit' => $this->usageLimit ? (int) $this->usageLimit : null,
                'per_user_limit' => (int) $this->perUserLimit,
                'is_active' => $this->isActive,
                'starts_at' => $this->startsAt ? \Carbon\Carbon::parse($this->startsAt) : null,
                'expires_at' => $this->expiresAt ? \Carbon\Carbon::parse($this->expiresAt) : null,
            ];

            if ($this->isEditing) {
                Promotion::findOrFail($this->editingId)->update($data);
                $this->dispatch('showAlert', ['type' => 'success', 'message' => 'Promo berhasil diperbarui!']);
            } else {
                Promotion::create($data);
                $this->dispatch('showAlert', ['type' => 'success', 'message' => 'Promo berhasil dibuat!']);
            }

            $this->closeModal();
        } catch (\Throwable $e) {
            $this->dispatch('showAlert', ['type' => 'error', 'message' => 'Gagal menyimpan promo. Silakan periksa data yang Anda masukkan.']);
            report($e);
        }
    }

    public function toggleActive(int $id): void
    {
        $promotion = Promotion::findOrFail($id);
        $promotion->update(['is_active' => !$promotion->is_active]);
    }

    public function confirmDelete(int $id): void
    {
        $this->dispatch('showConfirm', [
            'title' => 'Hapus Promo?',
            'message' => 'Apakah Anda yakin ingin menghapus promo ini? Tindakan ini tidak dapat dibatalkan.',
            'confirmText' => 'Hapus',
            'cancelText' => 'Batal',
            'confirmAction' => 'deletePromo',
            'componentId' => $this->getId(),
            'id' => $id,
        ]);
    }

    public function deletePromo(int $id): void
    {
        Promotion::findOrFail($id)->delete();
        $this->dispatch('showAlert', ['type' => 'success', 'message' => 'Promo berhasil dihapus!']);
    }

    public function generateCode(): void
    {
        $this->code = 'PROMO' . strtoupper(Str::random(4));
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Promotion::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('code', 'like', '%' . $this->search . '%')
                    ->orWhere('title', 'like', '%' . $this->search . '%');
            });
        }

        $promotions = $query->orderByDesc('created_at')->paginate(10);

        return view('livewire.admin.promotion-management', [
            'promotions' => $promotions,
        ]);
    }
}
