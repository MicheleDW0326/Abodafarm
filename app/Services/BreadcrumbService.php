<?php

namespace App\Services;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * Breadcrumb Service
 *
 * Automatically generates breadcrumb navigation based on current route.
 */
class BreadcrumbService
{
    /**
     * Base URL for the application.
     */
    protected string $baseUrl;

    /**
     * Breadcrumb items.
     */
    protected array $items = [];

    /**
     * Route definitions for breadcrumb generation.
     */
    protected array $routeDefinitions = [];

    /**
     * Custom labels for route parameters.
     */
    protected array $parameterLabels = [];

    public function __construct()
    {
        $this->baseUrl = rtrim(config('seo.canonical_base_url', config('app.url', 'https://abodafarm.test')), '/');
        $this->initializeRouteDefinitions();
    }

    /**
     * Initialize route definitions.
     */
    protected function initializeRouteDefinitions(): void
    {
        $this->routeDefinitions = [
            // Public pages
            '/' => ['name' => 'Beranda', 'icon' => 'fa-home'],

            // Shop pages
            'shop' => ['name' => 'Toko', 'parent' => '/'],
            'shop/{slug}' => ['name' => 'Produk', 'parent' => 'shop', 'label' => 'product'],

            // Category pages
            'shop' => ['name' => 'Toko', 'parent' => '/'],
            'kategori/{slug}' => ['name' => 'Kategori', 'parent' => 'shop', 'label' => 'category'],

            // Location pages
            'peternak' => ['name' => 'Peternak', 'parent' => '/'],
            'peternak/{province}' => ['name' => 'Provinsi', 'parent' => 'peternak', 'label' => 'province'],
            'peternak/{province}/{regency}' => ['name' => 'Kabupaten/Kota', 'parent' => 'peternak/{province}', 'label' => 'regency'],

            // Category location pages
            'ternak/{location}' => ['name' => 'Ternak', 'parent' => '/', 'label' => 'location'],
            'daging/{location}' => ['name' => 'Daging Segar', 'parent' => '/', 'label' => 'location'],
            'pakan/{location}' => ['name' => 'Pakan & Perlengkapan', 'parent' => '/', 'label' => 'location'],

            // Static pages
            'tentang-kami' => ['name' => 'Tentang Kami', 'parent' => '/'],
            'kontak' => ['name' => 'Kontak', 'parent' => '/'],
            'galeri' => ['name' => 'Galeri', 'parent' => '/'],

            // Checkout
            'checkout' => ['name' => 'Checkout', 'parent' => '/'],

            // Authentication
            'login' => ['name' => 'Masuk', 'parent' => '/'],
            'register' => ['name' => 'Daftar', 'parent' => '/'],

            // Customer dashboard
            'pelanggan' => ['name' => 'Dashboard', 'parent' => '/'],
            'pelanggan/profil' => ['name' => 'Profil', 'parent' => 'pelanggan'],
            'pelanggan/pesanan' => ['name' => 'Pesanan', 'parent' => 'pelanggan'],
            'pelanggan/booking' => ['name' => 'Booking', 'parent' => 'pelanggan'],
            'pelanggan/favorit' => ['name' => 'Favorit', 'parent' => 'pelanggan'],
            'pelanggan/alamat' => ['name' => 'Alamat', 'parent' => 'pelanggan'],

            // Farmer dashboard
            'peternak-dashboard' => ['name' => 'Dashboard', 'parent' => '/'],
            'peternak/ternak' => ['name' => 'Ternak', 'parent' => '/'],
            'peternak/pesanan' => ['name' => 'Pesanan', 'parent' => '/'],
            'peternak/pengiriman' => ['name' => 'Pengiriman', 'parent' => '/'],
            'peternak/profil' => ['name' => 'Profil', 'parent' => '/'],
            'peternak/ulasan' => ['name' => 'Ulasan', 'parent' => '/'],
            'peternak/dompet' => ['name' => 'Dompet', 'parent' => '/'],

            // Admin dashboard
            'admin' => ['name' => 'Admin', 'parent' => '/'],
            'admin/produk' => ['name' => 'Produk', 'parent' => 'admin'],
            'admin/pemesanan' => ['name' => 'Pemesanan', 'parent' => 'admin'],
            'admin/pelanggan' => ['name' => 'Pelanggan', 'parent' => 'admin'],
            'admin/staf' => ['name' => 'Staf', 'parent' => 'admin'],
            'admin/laporan-keuangan' => ['name' => 'Laporan Keuangan', 'parent' => 'admin'],
            'admin/notifikasi' => ['name' => 'Notifikasi', 'parent' => 'admin'],
            'admin/pengaturan-toko' => ['name' => 'Pengaturan Toko', 'parent' => 'admin'],

            // Staff dashboard
            'staff' => ['name' => 'Staf', 'parent' => '/'],
            'staff/pesanan' => ['name' => 'Pesanan', 'parent' => 'staff'],
            'staff/pengiriman' => ['name' => 'Pengiriman', 'parent' => 'staff'],
            'staff/operasional' => ['name' => 'Operasional', 'parent' => 'staff'],
        ];

        // Custom parameter labels (can be set dynamically)
        $this->parameterLabels = [];
    }

    /**
     * Generate breadcrumbs for current route.
     */
    public function generate(?array $customItems = null): array
    {
        $this->items = [];

        // Add home first
        $this->addItem('Beranda', '/', 'fa-home');

        // If custom items provided, add them
        if ($customItems) {
            foreach ($customItems as $item) {
                $name = $item['name'] ?? '';
                $url = $item['url'] ?? null;
                $icon = $item['icon'] ?? null;

                if ($name) {
                    $this->addItem($name, $url, $icon);
                }
            }
            return $this->items;
        }

        // Auto-detect from route
        $route = Route::current();
        if (!$route) {
            return $this->items;
        }

        $routeName = $route->getName() ?? '';
        $routePath = $route->uri();

        // Map route names to breadcrumb definitions
        $path = $this->mapRouteToPath($routeName, $routePath, $route);

        if ($path) {
            $this->buildFromPath($path, $route);
        } else {
            // Fallback: generate from URI segments
            $this->buildFromUri($routePath, $route);
        }

        return $this->items;
    }

    /**
     * Map route name to breadcrumb path.
     */
    protected function mapRouteToPath(string $routeName, string $routePath, $route): ?array
    {
        // Direct route name mapping
        $routeMap = [
            'home' => ['/', 'shop', 'shop/{slug}'],
            'shop' => ['/', 'shop'],
            'shop.product' => ['/', 'shop', 'shop/{slug}'],
            'tentang-kami' => ['/', 'tentang-kami'],
            'about' => ['/', 'tentang-kami'],
            'kontak' => ['/', 'kontak'],
            'contact' => ['/', 'kontak'],
            'galeri' => ['/', 'galeri'],
            'gallery' => ['/', 'galeri'],
            'checkout' => ['/', 'checkout'],
            'peternak.province' => ['/', 'peternak', 'peternak/{province}'],
            'peternak.regency' => ['/', 'peternak', 'peternak/{province}', 'peternak/{province}/{regency}'],
            'location.ternak' => ['/', 'ternak/{location}'],
            'location.daging' => ['/', 'daging/{location}'],
            'location.pakan' => ['/', 'pakan/{location}'],
            'pelanggan.dashboard' => ['/', 'pelanggan'],
            'pelanggan.profile' => ['/', 'pelanggan', 'pelanggan/profil'],
            'pelanggan.orders' => ['/', 'pelanggan', 'pelanggan/pesanan'],
            'pelanggan.bookings' => ['/', 'pelanggan', 'pelanggan/booking'],
            'pelanggan.favorites' => ['/', 'pelanggan', 'pelanggan/favorit'],
            'admin.dashboard' => ['/', 'admin'],
            'admin.products' => ['/', 'admin', 'admin/produk'],
            'admin.orders' => ['/', 'admin', 'admin/pemesanan'],
            'staff.dashboard' => ['/', 'staff'],
            'staff.orders' => ['/', 'staff', 'staff/pesanan'],
            'staff.shipping' => ['/', 'staff', 'staff/pengiriman'],
            'peternak.dashboard' => ['/', 'peternak-dashboard'],
            'peternak.livestock' => ['/', 'peternak/ternak'],
            'peternak.orders' => ['/', 'peternak/pesanan'],
        ];

        if (isset($routeMap[$routeName])) {
            return $routeMap[$routeName];
        }

        // Try to match by path pattern
        foreach ($this->routeDefinitions as $pattern => $def) {
            if ($this->pathMatches($routePath, $pattern)) {
                $path = [];
                $current = $pattern;

                while ($current) {
                    array_unshift($path, $current);
                    $parent = $this->routeDefinitions[$current]['parent'] ?? null;
                    $current = $parent;
                }

                return $path;
            }
        }

        return null;
    }

    /**
     * Check if path matches pattern.
     */
    protected function pathMatches(string $path, string $pattern): bool
    {
        $pathSegments = explode('/', trim($path, '/'));
        $patternSegments = explode('/', trim($pattern, '/'));

        if (count($pathSegments) !== count($patternSegments)) {
            return false;
        }

        foreach ($patternSegments as $i => $segment) {
            if (Str::startsWith($segment, '{')) {
                continue; // Parameter, matches anything
            }
            if ($segment !== $pathSegments[$i]) {
                return false;
            }
        }

        return true;
    }

    /**
     * Build breadcrumbs from path.
     */
    protected function buildFromPath(array $path, $route): void
    {
        foreach ($path as $segment) {
            $def = $this->routeDefinitions[$segment] ?? null;

            if (!$def) {
                continue;
            }

            // Replace parameters in segment
            $url = $this->replaceParameters($segment, $route);
            $name = $def['name'];

            // Check for dynamic label
            if (isset($def['label'])) {
                $paramName = $def['label'];
                $paramValue = $route->parameter($paramName);

                if ($paramValue) {
                    $name = $this->formatLabel($paramValue, $def['label']);
                }
            }

            // Check custom labels
            $paramName = $def['label'] ?? null;
            if ($paramName && isset($this->parameterLabels[$paramName])) {
                $name = $this->parameterLabels[$paramName];
            }

            // Don't add link to last item
            $urlForItem = ($segment === end($path)) ? null : $url;

            $this->addItem($name, $urlForItem);
        }
    }

    /**
     * Build breadcrumbs from URI segments.
     */
    protected function buildFromUri(string $uri, $route): void
    {
        $segments = array_filter(explode('/', $uri));
        $path = '';

        foreach ($segments as $segment) {
            $path .= '/' . $segment;

            // Check if it's a parameter
            if (Str::startsWith($segment, '{') || Str::startsWith($segment, '?')) {
                $paramName = trim($segment, '{}?');
                $paramValue = $route->parameter($paramName) ?? $segment;

                // Check custom labels first
                if (isset($this->parameterLabels[$paramName])) {
                    $this->addItem($this->parameterLabels[$paramName]);
                } else {
                    $this->addItem($this->formatLabel($paramValue, $paramName));
                }
            } else {
                $def = $this->routeDefinitions[$path] ?? null;
                $name = $def['name'] ?? $this->formatLabel($segment, '');

                $this->addItem($name, $path);
            }
        }
    }

    /**
     * Replace parameters in path segment.
     */
    protected function replaceParameters(string $segment, $route): string
    {
        $result = $segment;

        // Find all parameters in the segment
        preg_match_all('/\{([^}]+)\}/', $segment, $matches);

        foreach ($matches[1] as $param) {
            $value = $route->parameter($param);

            if ($value) {
                // Check custom labels
                if (isset($this->parameterLabels[$param])) {
                    $result = str_replace('{' . $param . '}', $this->parameterLabels[$param], $result);
                } else {
                    $result = str_replace('{' . $param . '}', $value, $result);
                }
            }
        }

        // If no replacement happened, use the segment as-is
        if ($result === $segment) {
            // Extract the actual value from the route
            preg_match_all('/\{([^}]+)\}/', $segment, $matches);
            foreach ($matches[1] as $param) {
                $value = $route->parameter($param);
                if ($value) {
                    $result = str_replace('{' . $param . '}', $value, $result);
                }
            }
        }

        return $result;
    }

    /**
     * Format label from parameter value.
     */
    protected function formatLabel(string $value, string $type): string
    {
        // Convert slug to readable text
        $formatted = Str::title(str_replace('-', ' ', $value));

        // Type-specific formatting
        return match ($type) {
            'province' => $formatted,
            'regency' => $formatted,
            'location' => $formatted,
            'slug', 'product' => $formatted,
            'category' => $formatted,
            default => $formatted,
        };
    }

    /**
     * Add item to breadcrumbs.
     */
    public function addItem(string $name, ?string $url = null, ?string $icon = null): void
    {
        $this->items[] = [
            'name' => $name,
            'url' => $url ? $this->normalizeUrl($url) : null,
            'icon' => $icon,
        ];
    }

    /**
     * Set custom parameter label.
     */
    public function setParameterLabel(string $param, string $label): self
    {
        $this->parameterLabels[$param] = $label;
        return $this;
    }

    /**
     * Set multiple parameter labels.
     */
    public function setParameterLabels(array $labels): self
    {
        foreach ($labels as $param => $label) {
            $this->parameterLabels[$param] = $label;
        }
        return $this;
    }

    /**
     * Clear all items.
     */
    public function clear(): self
    {
        $this->items = [];
        $this->parameterLabels = [];
        return $this;
    }

    /**
     * Normalize URL to absolute.
     */
    protected function normalizeUrl(string $url): string
    {
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return $this->baseUrl . $url;
    }

    /**
     * Generate JSON-LD schema for breadcrumbs.
     */
    public function toJsonLd(): ?string
    {
        if (count($this->items) < 2) {
            return null;
        }

        $items = [];
        foreach ($this->items as $index => $item) {
            $listItem = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item['name'],
            ];

            if ($item['url']) {
                $listItem['item'] = $item['url'];
            }

            $items[] = $listItem;
        }

        return json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get items.
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Static factory method.
     */
    public static function make(): self
    {
        return new self();
    }
}
