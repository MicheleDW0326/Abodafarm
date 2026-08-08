<?php

namespace App\Livewire\Farmer\Analytics;

use App\Services\FarmerAnalyticsService;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.main')]
#[Title('Dashboard Analytics - Farmer AbodaFarm')]
class Dashboard extends Component
{
    public string $dateRange = 'last_30_days';
    public ?string $startDate = null;
    public ?string $endDate = null;

    public string $chartType = 'revenue';

    public FarmerAnalyticsService $analytics;

    public function mount(): void
    {
        $this->analytics = $this->getAnalyticsService();
    }

    public function getAnalyticsService(): FarmerAnalyticsService
    {
        $dates = $this->getDateRange();
        return new FarmerAnalyticsService(
            auth()->id(),
            $dates['start'],
            $dates['end']
        );
    }

    public function getDateRange(): array
    {
        return match ($this->dateRange) {
            'today' => [
                'start' => now()->startOfDay(),
                'end' => now()->endOfDay(),
            ],
            'yesterday' => [
                'start' => now()->subDay()->startOfDay(),
                'end' => now()->subDay()->endOfDay(),
            ],
            'last_7_days' => [
                'start' => now()->subDays(7)->startOfDay(),
                'end' => now()->endOfDay(),
            ],
            'last_30_days' => [
                'start' => now()->subDays(30)->startOfDay(),
                'end' => now()->endOfDay(),
            ],
            'this_month' => [
                'start' => now()->startOfMonth(),
                'end' => now()->endOfDay(),
            ],
            'last_month' => [
                'start' => now()->subMonth()->startOfMonth(),
                'end' => now()->subMonth()->endOfMonth(),
            ],
            'custom' => [
                'start' => $this->startDate ? Carbon::parse($this->startDate)->startOfDay() : now()->subDays(30)->startOfDay(),
                'end' => $this->endDate ? Carbon::parse($this->endDate)->endOfDay() : now()->endOfDay(),
            ],
            default => [
                'start' => now()->subDays(30)->startOfDay(),
                'end' => now()->endOfDay(),
            ],
        };
    }

    public function updatedDateRange(): void
    {
        $this->analytics = $this->getAnalyticsService();
    }

    public function updatedStartDate(): void
    {
        if ($this->startDate && $this->endDate) {
            $this->dateRange = 'custom';
            $this->analytics = $this->getAnalyticsService();
        }
    }

    public function updatedEndDate(): void
    {
        if ($this->startDate && $this->endDate) {
            $this->dateRange = 'custom';
            $this->analytics = $this->getAnalyticsService();
        }
    }

    public function getKpiCards(): array
    {
        return $this->analytics->getKpiCards();
    }

    public function getSalesData(): array
    {
        return $this->analytics->getDailySales(30);
    }

    public function getMonthlySalesData(): array
    {
        return $this->analytics->getMonthlySales(12);
    }

    public function getTopProducts(): array
    {
        return $this->analytics->getTopSellingProducts(10);
    }

    public function getLowStockProducts(): array
    {
        return $this->analytics->getLowStockProducts(5);
    }

    public function getOutOfStockProducts(): array
    {
        return $this->analytics->getOutOfStockProducts(5);
    }

    public function getRecentReviews(): array
    {
        return $this->analytics->getRecentReviews(5);
    }

    public function getRatingDistribution(): array
    {
        return $this->analytics->getRatingDistribution();
    }

    public function getFinancialSummary(): array
    {
        return $this->analytics->getFinancialSummary();
    }

    public function getFarmProfile()
    {
        return auth()->user()->farmProfile;
    }

    public function render()
    {
        return view('livewire.farmer.analytics.dashboard', [
            'kpiCards' => $this->getKpiCards(),
            'salesData' => $this->getSalesData(),
            'monthlySalesData' => $this->getMonthlySalesData(),
            'topProducts' => $this->getTopProducts(),
            'lowStockProducts' => $this->getLowStockProducts(),
            'outOfStockProducts' => $this->getOutOfStockProducts(),
            'recentReviews' => $this->getRecentReviews(),
            'ratingDistribution' => $this->getRatingDistribution(),
            'financialSummary' => $this->getFinancialSummary(),
            'farmProfile' => $this->getFarmProfile(),
        ]);
    }
}
