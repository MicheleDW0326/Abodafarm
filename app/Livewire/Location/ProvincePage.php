<?php

namespace App\Livewire\Location;

use App\Models\FarmProfile;
use App\Models\Product;
use App\Services\LocalSeoService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ProvincePage extends Component
{
    public string $province;

    protected LocalSeoService $seoService;

    public function mount(string $province): void
    {
        $this->province = $this->formatProvince($province);
        $this->seoService = app(LocalSeoService::class);
    }

    public function render()
    {
        return view('livewire.location.province-page')
            ->layout('layouts.guest-home');
    }

    #[Computed]
    public function seoData(): array
    {
        return $this->seoService->getProvinceSeo($this->province);
    }

    #[Computed]
    public function farmers(): Collection
    {
        return $this->seoService->getNearbyFarmers($this->province, LocalSeoService::LOCATION_PROVINCE, 12);
    }

    #[Computed]
    public function featuredProducts(): Collection
    {
        return $this->seoService->getPopularProductsByLocation($this->province, LocalSeoService::LOCATION_PROVINCE, 8);
    }

    #[Computed]
    public function latestProducts(): Collection
    {
        return $this->seoService->getLatestProductsByLocation($this->province, LocalSeoService::LOCATION_PROVINCE, 8);
    }

    #[Computed]
    public function relatedLocations(): array
    {
        return $this->seoService->getRelatedLocations($this->province, LocalSeoService::LOCATION_PROVINCE);
    }

    #[Computed]
    public function regencies(): Collection
    {
        return FarmProfile::where('province', $this->province)
            ->whereNotNull('regency')
            ->selectRaw('regency, COUNT(*) as farm_count, AVG(rating) as avg_rating')
            ->groupBy('regency')
            ->orderByDesc('farm_count')
            ->get();
    }

    #[Computed]
    public function stats(): array
    {
        $farmCount = FarmProfile::where('province', $this->province)->count();
        $farmIds = FarmProfile::where('province', $this->province)->pluck('user_id');
        $productCount = Product::whereIn('owner_id', $farmIds)->count();

        return [
            'farmers' => $farmCount,
            'products' => $productCount,
            'regencies' => $this->regencies->count(),
        ];
    }

    #[Computed]
    public function localBusinessSchema(): ?array
    {
        return $this->seoService->generateLocalBusinessSchema($this->province, LocalSeoService::LOCATION_PROVINCE);
    }

    protected function formatProvince(string $province): string
    {
        // Convert slug to proper province name
        return str_replace('-', ' ', ucwords(str_replace('-', ' ', $province)));
    }
}
