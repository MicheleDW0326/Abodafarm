<?php

namespace App\Livewire\Smart;

use App\Services\Smart\FarmerInsightsService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.main')]
class FarmerInsights extends Component
{
    public array $insights = [];
    public bool $isLoading = true;

    protected FarmerInsightsService $insightsService;

    public function boot(FarmerInsightsService $insightsService): void
    {
        $this->insightsService = $insightsService;
    }

    public function mount(): void
    {
        $this->loadInsights();
    }

    public function loadInsights(): void
    {
        $this->isLoading = true;

        $service = new FarmerInsightsService(auth()->id());
        $this->insights = $service->getInsights();

        $this->isLoading = false;
    }

    public function refresh(): void
    {
        $this->insightsService->clearCache();
        $this->loadInsights();
    }

    public function render()
    {
        return view('livewire.smart.farmer-insights');
    }
}
