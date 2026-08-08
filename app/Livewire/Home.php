<?php

namespace App\Livewire;

use App\Models\{Category, Faq, FarmStatistic, HeroSlide, Product, ProductView, Review, Testimonial};
use App\Services\RecommendationService;
use App\Services\SeoService;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.guest-home')]
class Home extends Component
{
    public string $search = '';

    public array $heroSlides = [];
    public array $farmStats = [];
    public array $categories = [];
    public array $highlights = [];
    public array $deliverySteps = [];
    public array $careStages = [];
    public array $testimonials = [];
    public array $faqs = [];

    // Products for main section
    public array $filteredLiveLivestockProducts = [];
    public array $freshMeatProducts = [];

    // Recommendation Sections
    public array $newArrivals = [];
    public array $popularProducts = [];
    public array $trendingProducts = [];
    public array $topRatedProducts = [];
    public array $organicProducts = [];
    public array $recommendedProducts = [];
    public array $recentlyViewed = [];
    public array $continueShopping = [];

    protected RecommendationService $recommendationService;

    public function mount(RecommendationService $recommendationService): void
    {
        $this->recommendationService = $recommendationService;

        $this->loadHeroSlides();
        $this->loadFarmStats();
        $this->loadCategories();
        $this->loadStaticContent();
        $this->loadProducts();
        $this->loadRecommendations();
    }

    protected function loadHeroSlides(): void
    {
        $slides = HeroSlide::active()->ordered()->get();

        $this->heroSlides = $slides->map(fn($slide) => [
            'title' => $slide->title,
            'description' => $slide->subtitle ?? '',
            'image' => $slide->image_url,
            'button_text' => $slide->button_text,
            'button_url' => $slide->button_url,
        ])->toArray();

        if (empty($this->heroSlides)) {
            $this->heroSlides = [
                [
                    'title' => 'Ternak Organik Berkualitas Langsung dari Peternakan',
                    'description' => 'Dapatkan sapi, kambing, dan ayam organik dengan jaminan kesehatan, bobot jelas, dan kualitas terbaik.',
                    'image' => 'https://images.unsplash.com/photo-1464226184884-fa280b87c399?auto=format&fit=crop&w=1600&q=80',
                    'button_text' => null,
                    'button_url' => null,
                ],
                [
                    'title' => 'Transparan, Aman, dan Siap Dibooking',
                    'description' => 'Riwayat perawatan, sertifikat kesehatan, dan update kondisi ternak membantu pembeli mengambil keputusan yang lebih yakin.',
                    'image' => 'https://images.unsplash.com/photo-1511538897584-1ad2a1d7d3d4?auto=format&fit=crop&w=1600&q=80',
                    'button_text' => null,
                    'button_url' => null,
                ],
                [
                    'title' => 'Siap Kirim untuk Rumah, Resto, dan Mitra Usaha',
                    'description' => 'Booking jadi lebih mudah dengan alur yang jelas dari pemilihan ternak hingga pengiriman atau pengambilan.',
                    'image' => 'https://images.unsplash.com/photo-1547487704-5f9f3fc7b6b0?auto=format&fit=crop&w=1600&q=80',
                    'button_text' => null,
                    'button_url' => null,
                ],
            ];
        }
    }

    protected function loadFarmStats(): void
    {
        $stats = FarmStatistic::ordered()->get();

        $this->farmStats = $stats->map(fn($stat) => [
            'value' => $stat->value,
            'label' => $stat->label,
            'icon' => $stat->icon,
        ])->toArray();

        if (empty($this->farmStats)) {
            $this->farmStats = [
                ['value' => '500+', 'label' => 'Ternak Terjual', 'icon' => '🦄'],
                ['value' => '100+', 'label' => 'Pelanggan', 'icon' => '👥'],
                ['value' => '50+', 'label' => 'Peternak Mitra', 'icon' => '🐄'],
                ['value' => '98%', 'label' => 'Kepuasan Pelanggan', 'icon' => '⭐'],
            ];
        }
    }

    protected function loadCategories(): void
    {
        $categories = Category::active()->ordered()->get();

        $this->categories = $categories->map(fn($cat) => [
            'id' => $cat->id,
            'name' => $cat->name,
            'description' => $cat->description ?? '',
            'image' => $cat->image_url,
            'icon' => $cat->icon ?? '',
            'search' => Str::slug($cat->name),
        ])->toArray();

        if (empty($this->categories)) {
            $this->categories = [
                ['id' => 1, 'name' => 'Sapi', 'description' => 'Sapi organik siap jual dengan informasi bobot dan kondisi yang jelas.', 'image' => 'https://images.unsplash.com/photo-1548199973-03cce0bbc87b?auto=format&fit=crop&w=800&q=80', 'icon' => '🐄', 'search' => 'sapi'],
                ['id' => 2, 'name' => 'Kambing', 'description' => 'Kambing sehat untuk aqiqah, kurban, atau kebutuhan usaha.', 'image' => 'https://images.unsplash.com/photo-1516291073093-5a6c5f5f4a1a?auto=format&fit=crop&w=800&q=80', 'icon' => '🐐', 'search' => 'kambing'],
                ['id' => 3, 'name' => 'Ayam', 'description' => 'Ayam organik dengan perawatan berkala dan pakan terkontrol.', 'image' => 'https://images.unsplash.com/photo-1511823794984-b87716139b88?auto=format&fit=crop&w=800&q=80', 'icon' => '🐔', 'search' => 'ayam'],
                ['id' => 4, 'name' => 'Bebek', 'description' => 'Bebek sehat untuk kebutuhan rumah tangga dan kuliner.', 'image' => 'https://images.unsplash.com/photo-1507358522600-9f71e620c44e?auto=format&fit=crop&w=800&q=80', 'icon' => '🦆', 'search' => 'bebek'],
            ];
        }
    }

    protected function loadProducts(): void
    {
        // Live livestock products (inquiry type)
        $liveProducts = Product::live()
            ->availableWithStatus()
            ->with('category')
            ->get();

        $this->filteredLiveLivestockProducts = $liveProducts->map(fn($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'price' => (int) $p->price,
            'category' => $p->category?->slug ?? '',
            'category_name' => $p->category?->name ?? '',
            'image' => $p->image_url,
            'weight' => $p->weight ? $p->weight . ' Kg' : '',
            'age' => $p->age ?? '',
            'sex' => $p->sex_label,
            'sex_label' => $p->sex_label,
            'location' => $p->origin ?? '',
            'status' => $p->status_label,
            'status_label' => $p->status_label,
        ])->toArray();

        // Fresh meat products (cart type with stock)
        $freshProducts = Product::freshType()
            ->available()
            ->inStock()
            ->with('category')
            ->get();

        $this->freshMeatProducts = $freshProducts->map(fn($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'price' => (int) $p->price,
            'category' => $p->category?->slug ?? '',
            'category_name' => $p->category?->name ?? '',
            'image' => $p->image_url,
            'stock' => $p->stock,
            'stock_status' => $p->stock_status,
            'stock_status_label' => $p->stock_status_label,
        ])->toArray();
    }

    protected function loadStaticContent(): void
    {
        // Highlights
        $this->highlights = [
            ['title' => 'Organik', 'description' => 'Ternak dipelihara secara alami dan sehat dengan pakan yang terkontrol.'],
            ['title' => 'Pemeriksaan Kesehatan', 'description' => 'Seluruh ternak diperiksa secara berkala oleh tenaga berpengalaman.'],
            ['title' => 'Pengiriman Aman', 'description' => 'Pengiriman ternak dilakukan sesuai standar untuk menjaga kondisi hewan.'],
            ['title' => 'Transparansi', 'description' => 'Data bobot, umur, dan kondisi ternak tersedia sebelum booking.'],
        ];

        // Delivery Steps
        $this->deliverySteps = [
            ['title' => 'Pilih Ternak', 'description' => 'Gunakan katalog dan filter untuk menemukan ternak yang paling sesuai.'],
            ['title' => 'Lakukan Booking', 'description' => 'Ajukan booking atau pre-order sesuai status ternak yang dipilih.'],
            ['title' => 'Konfirmasi Pembayaran', 'description' => 'Tim kami akan memvalidasi pembayaran dan menyiapkan ternak.'],
            ['title' => 'Pengiriman / Pengambilan', 'description' => 'Ternak dikirim atau diambil sesuai area layanan dan jadwal yang disepakati.'],
        ];

        // Care Stages
        $this->careStages = [
            ['title' => 'Pakan Organik', 'description' => 'Pakan alami dipantau agar pertumbuhan ternak tetap sehat dan stabil.'],
            ['title' => 'Pemeriksaan Rutin', 'description' => 'Pengecekan kondisi fisik dan bobot dilakukan secara berkala.'],
            ['title' => 'Vaksinasi', 'description' => 'Program kesehatan mengikuti jadwal vaksin dan rekomendasi dokter hewan.'],
            ['title' => 'Siap Jual', 'description' => 'Ternak yang lolos standar kesehatan ditandai siap untuk booking.'],
        ];

        // Testimonials
        $testimonialItems = Testimonial::active()->ordered()->limit(6)->get()
            ->map(fn($t) => [
                'name' => $t->name,
                'role' => $t->role,
                'quote' => $t->content,
                'avatar' => $t->avatar,
                'rating' => $t->rating,
            ])->toArray();

        if (empty($testimonialItems)) {
            $reviews = Review::approved()->visible()
                ->whereNotNull('content')
                ->where('content', '!=', '')
                ->with(['user.farmProfile'])
                ->orderByDesc('created_at')
                ->limit(6)
                ->get();

            $testimonialItems = $reviews->map(fn($r) => [
                'name' => $r->user?->name ?? 'Pelanggan',
                'role' => 'Pelanggan',
                'quote' => $r->content,
                'avatar' => $r->user?->farmProfile?->image ? asset('storage/' . $r->user->farmProfile->image) : null,
                'rating' => $r->rating,
            ])->toArray();
        }

        $this->testimonials = $testimonialItems;

        if (empty($this->testimonials)) {
            $this->testimonials = [
                ['name' => 'Nadia', 'role' => 'Ibu Rumah Tangga', 'quote' => 'Ternaknya rapi dan daging segarnya terasa berkualitas. Traceability bikin percaya.', 'avatar' => null, 'rating' => 5],
                ['name' => 'Rian', 'role' => 'Owner Resto', 'quote' => 'Konsisten untuk kebutuhan dapur restoran—fresh, rapi, dan pengiriman tetap terjaga.', 'avatar' => null, 'rating' => 5],
                ['name' => 'Salsa', 'role' => 'Pecinta Masak', 'quote' => 'Kualitas premium dan tampilannya clean. Proses inquiry ternak hidup juga mudah.', 'avatar' => null, 'rating' => 5],
            ];
        }

        // FAQs
        $faqs = Faq::active()->ordered()->limit(8)->get()
            ->map(fn($f) => [
                'question' => $f->question,
                'answer' => $f->answer,
            ])->toArray();

        $this->faqs = $faqs;

        if (empty($this->faqs)) {
            $this->faqs = [
                ['question' => 'Apakah ternak sudah vaksin?', 'answer' => 'Ya, seluruh ternak menjalani pemeriksaan kesehatan berkala dan vaksinasi sesuai jadwal.'],
                ['question' => 'Apakah bisa dikirim keluar daerah?', 'answer' => 'Bisa, selama area tujuan masih berada dalam jangkauan layanan pengiriman kami.'],
                ['question' => 'Bagaimana jika stok habis?', 'answer' => 'Anda bisa melakukan pre-order dan kami akan menghubungi saat ternak siap tersedia.'],
                ['question' => 'Apakah ada sertifikat kesehatan?', 'answer' => 'Untuk ternak tertentu, sertifikat kesehatan dan hasil pemeriksaan dapat diminta saat booking.'],
            ];
        }
    }

    protected function loadRecommendations(): void
    {
        $userId = auth()->id();

        // New Arrivals
        $newArrivals = $this->recommendationService->getNewest(12);
        $this->newArrivals = $this->recommendationService->formatProducts($newArrivals);

        // Popular Products
        $popular = $this->recommendationService->getPopular(12);
        $this->popularProducts = $this->recommendationService->formatProducts($popular);

        // Trending This Week
        $trending = $this->recommendationService->getTrending(12);
        $this->trendingProducts = $this->recommendationService->formatProducts($trending);

        // Top Rated
        $topRated = $this->recommendationService->getTopRated(12);
        $this->topRatedProducts = $this->recommendationService->formatProducts($topRated);

        // Organic Choice
        $organic = $this->recommendationService->getOrganicChoice(12);
        $this->organicProducts = $this->recommendationService->formatProducts($organic);

        // Recently Viewed
        $recentlyViewed = $this->recommendationService->getRecentlyViewed($userId, 12);
        $this->recentlyViewed = $this->recommendationService->formatProducts($recentlyViewed);

        // Continue Shopping (same as recently viewed but different display)
        $continueShopping = $this->recommendationService->getContinueShopping($userId, 6);
        $this->continueShopping = $this->recommendationService->formatProducts($continueShopping);

        // Personalized Recommendations (for logged in users)
        if ($userId) {
            $recommended = $this->recommendationService->getRecommendedForUser($userId, 12);
            $this->recommendedProducts = $this->recommendationService->formatProducts($recommended);
        }
    }

    public function updatedSearch(): void
    {
        $this->dispatch('search-updated', search: $this->search);
    }

    public function render()
    {
        $seoService = app(SeoService::class);
        $seo = $seoService->forHomepage();

        return view('livewire.home', [
            'seo' => $seo,
        ]);
    }
}
