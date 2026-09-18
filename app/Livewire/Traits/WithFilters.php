<?php

namespace App\Livewire\Traits;

trait WithFilters
{
    public array $filters = [];
    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilters(): void
    {
        $this->resetPage();
    }

    public function setFilter(string $key, mixed $value): void
    {
        $this->filters[$key] = $value;
        $this->resetPage();
    }

    public function clearFilter(string $key): void
    {
        unset($this->filters[$key]);
        $this->resetPage();
    }

    public function clearAllFilters(): void
    {
        $this->filters = [];
        $this->search = '';
        $this->resetPage();
    }

    public function getActiveFiltersCount(): int
    {
        return count(array_filter($this->filters)) + ($this->search ? 1 : 0);
    }

    public function hasActiveFilters(): bool
    {
        return $this->getActiveFiltersCount() > 0;
    }
}
