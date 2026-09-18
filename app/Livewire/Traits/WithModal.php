<?php

namespace App\Livewire\Traits;

trait WithModal
{
    public bool $showModal = false;
    public ?int $editingId = null;
    public string $modalMode = 'create';

    public function openModal(?int $id = null): void
    {
        $this->editingId = $id;
        $this->modalMode = $id ? 'edit' : 'create';
        $this->showModal = true;
        $this->resetErrorBag();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->editingId = null;
        $this->modalMode = 'create';
        $this->resetErrorBag();
    }

    public function isEditing(): bool
    {
        return $this->modalMode === 'edit';
    }

    public function isCreating(): bool
    {
        return $this->modalMode === 'create';
    }

    public function getModalTitle(): string
    {
        return $this->isEditing() ? 'Edit' : 'Tambah';
    }

    public function confirmDelete(int $id): void
    {
        $this->dispatch('confirm-delete', id: $id);
    }
}
