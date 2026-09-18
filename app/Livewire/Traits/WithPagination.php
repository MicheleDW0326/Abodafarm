<?php

namespace App\Livewire\Traits;

trait WithPagination
{
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function getSortIcon(string $field): string
    {
        if ($this->sortField !== $field) {
            return 'fa-sort';
        }

        return $this->sortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down';
    }

    public function isSorted(string $field): bool
    {
        return $this->sortField === $field;
    }

    public function resetSort(): void
    {
        $this->sortField = 'created_at';
        $this->sortDirection = 'desc';
    }
}
