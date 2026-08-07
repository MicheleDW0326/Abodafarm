<?php

namespace App\Livewire\Location;

use App\Models\FarmProfile;
use App\Models\Product;
use App\Services\LocalSeoService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class RegencyPage extends Component
{
    public string $province;
    public string $regency;

    protected LocalSeoService $seoService;

    public function mount(string $province, string $regency): void
    {
        $this->province = $this->formatProvince($province);
        $this->regency = $this->formatRegency($regency);
        $this->seoService = app(LocalSeoService::class);
    }

    public function render()
    {
        return view('livewire.location.regency-page')
            ->layout('layouts.guest-home');
    }

    #[Computed]
    public function seoData(): array
    {
        return $this->seoService->getRegencySeo($this->province, $this->regency);
    }

    #[Computed]
    public function farmers(): Collection
    {
        return $this->seoService->getNearbyFarmers($this->regency, LocalSeoService::LOCATION_REGENCY, 12);
    }

    #[Computed]
    public function featuredProducts(): Collection
    {
        return $this->seoService->getPopularProductsByLocation($this->regency, LocalSeoService::LOCATION_REGENCY, 8);
    }

    #[Computed]
    public function latestProducts(): Collection
    {
        return $this->seoService->getLatestProductsByLocation($this->regency, LocalSeoService::LOCATION_REGENCY, 8);
    }

    #[Computed]
    public function relatedLocations(): array
    {
        return $this->seoService->getRelatedLocations($this->regency, LocalSeoService::LOCATION_REGENCY);
    }

    #[Computed]
    public function districts(): Collection
    {
        return FarmProfile::where('regency', $this->regency)
            ->whereNotNull('district')
            ->selectRaw('district, COUNT(*) as farm_count, AVG(rating) as avg_rating')
            ->groupBy('district')
            ->orderByDesc('farm_count')
            ->get();
    }

    #[Computed]
    public function stats(): array
    {
        $farmCount = FarmProfile::where('regency', $this->regency)->count();
        $farmIds = FarmProfile::where('regency', $this->regency)->pluck('user_id');
        $productCount = Product::whereIn('owner_id', $farmIds)->count();

        return [
            'farmers' => $farmCount,
            'products' => $productCount,
            'districts' => $this->districts->count(),
        ];
    }

    #[Computed]
    public function localBusinessSchema(): ?array
    {
        return $this->seoService->generateLocalBusinessSchema($this->regency, LocalSeoService::LOCATION_REGENCY);
    }

    protected function formatProvince(string $province): string
    {
        return str_replace('-', ' ', ucwords(str_replace('-', ' ', $province)));
    }

    protected function formatRegency(string $regency): string
    {
        return preg_replace('/^(kabupaten|kota)\s+/i', '', str_replace('-', ' ', ucwords(str_replace('-', ' ', $regency))));
    }
}
