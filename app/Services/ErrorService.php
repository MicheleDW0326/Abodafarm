<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

/**
 * Error Page Service
 *
 * Provides data for SEO-friendly error pages.
 */
class ErrorService
{
    /**
     * Get popular categories.
     */
    public function getCategories(int $limit = 8): array
    {
        try {
            return Category::active()
                ->ordered()
                ->limit($limit)
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            // Fallback to empty array if database is unavailable
            return [];
        }
    }

    /**
     * Get latest products.
     */
    public function getLatestProducts(int $limit = 4): array
    {
        try {
            return Product::availableWithStatus()
                ->inStock()
                ->with('category')
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get()
                ->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'slug' => $product->slug,
                        'price' => $product->formatted_current_price,
                        'image_url' => $product->image_url,
                        'category' => $product->category,
                    ];
                })
                ->toArray();
        } catch (\Exception $e) {
            // Fallback to empty array if database is unavailable
            return [];
        }
    }

    /**
     * Get featured products.
     */
    public function getFeaturedProducts(int $limit = 4): array
    {
        try {
            return Product::availableWithStatus()
                ->inStock()
                ->featured()
                ->with('category')
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get()
                ->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'slug' => $product->slug,
                        'price' => $product->formatted_current_price,
                        'image_url' => $product->image_url,
                        'category' => $product->category,
                    ];
                })
                ->toArray();
        } catch (\Exception $e) {
            // Fallback to empty array if database is unavailable
            return [];
        }
    }

    /**
     * Get error page data.
     */
    public function getErrorPageData(int $code = 404): array
    {
        $errorMessages = [
            404 => [
                'title' => 'Halaman Tidak Ditemukan',
                'code' => '404',
                'heading' => 'Oops! Ternak Ini Lari ke Lainnya',
                'message' => 'Maaf, halaman yang Anda cari tidak ditemukan. Mungkin sudah dipindahkan atau tidak lagi tersedia.',
                'search_placeholder' => 'Cari sapi, kambing, pakan...',
            ],
            500 => [
                'title' => 'Terjadi Kesalahan Server',
                'code' => '500',
                'heading' => 'Oops! Ada Masalah di Kandang',
                'message' => 'Maaf, terjadi kesalahan di server kami. Tim kami sedang bekerja untuk memperbaikinya.',
                'search_placeholder' => 'Cari produk lainnya...',
            ],
            403 => [
                'title' => 'Akses Ditolak',
                'code' => '403',
                'heading' => 'Akses Terbatas',
                'message' => 'Maaf, Anda tidak memiliki izin untuk mengakses halaman ini.',
                'search_placeholder' => 'Cari halaman lain...',
            ],
            419 => [
                'title' => 'Sesi Berakhir',
                'code' => '419',
                'heading' => 'Sesi Anda Telah Berakhir',
                'message' => 'Maaf, sesi Anda telah berakhir. Silakan refresh halaman dan coba lagi.',
                'search_placeholder' => 'Cari produk...',
            ],
            429 => [
                'title' => 'Terlalu Banyak Permintaan',
                'code' => '429',
                'heading' => 'perlalu Banyak Permintaan',
                'message' => 'Maaf, Anda telah melakukan terlalu banyak permintaan. Silakan tunggu beberapa saat dan coba lagi.',
                'search_placeholder' => 'Cari produk...',
            ],
        ];

        $data = $errorMessages[$code] ?? $errorMessages[404];
        $data['categories'] = $this->getCategories();
        $data['products'] = $this->getLatestProducts();

        return $data;
    }
}
