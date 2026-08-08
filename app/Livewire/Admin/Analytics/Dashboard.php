<?php

namespace App\Livewire\Admin\Analytics;

use App\Services\AnalyticsService;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.main')]
#[Title('Dashboard Analytics - Admin AbodaFarm')]
class Dashboard extends Component
{
    public string $dateRange = 'last_30_days';
    public ?string $startDate = null;
    public ?string $endDate = null;

    public string $salesPeriod = 'daily';
    public string $chartType = 'revenue';

    public AnalyticsService $analytics;

    public function mount(): void
    {
        $this->analytics = $this->getAnalyticsService();
    }

    public function getAnalyticsService(): AnalyticsService
    {
        $dates = $this->getDateRange();
        return new AnalyticsService($dates['start'], $dates['end']);
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
        return match ($this->salesPeriod) {
            'daily' => $this->analytics->getDailySales(30),
            'weekly' => $this->analytics->getWeeklySales(12),
            'monthly' => $this->analytics->getMonthlySales(12),
            'yearly' => $this->analytics->getYearlySales(5),
            default => $this->analytics->getDailySales(30),
        };
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

    public function getTopFarmers(): array
    {
        return $this->analytics->getTopFarmers(5);
    }

    public function getNewCustomers(): array
    {
        return $this->analytics->getNewCustomers(5);
    }

    public function getTopBuyers(): array
    {
        return $this->analytics->getTopBuyers(5);
    }

    public function getFinancialSummary(): array
    {
        return $this->analytics->getFinancialSummary();
    }

    public function getCustomerGrowth(): array
    {
        return $this->analytics->getCustomerGrowth();
    }

    public function getBookingStats(): array
    {
        return $this->analytics->getBookingStats();
    }

    public function render()
    {
        return view('livewire.admin.analytics.dashboard', [
            'kpiCards' => $this->getKpiCards(),
            'salesData' => $this->getSalesData(),
            'topProducts' => $this->getTopProducts(),
            'lowStockProducts' => $this->getLowStockProducts(),
            'outOfStockProducts' => $this->getOutOfStockProducts(),
            'topFarmers' => $this->getTopFarmers(),
            'newCustomers' => $this->getNewCustomers(),
            'topBuyers' => $this->getTopBuyers(),
            'financialSummary' => $this->getFinancialSummary(),
            'customerGrowth' => $this->getCustomerGrowth(),
            'bookingStats' => $this->getBookingStats(),
        ]);
    }
}
