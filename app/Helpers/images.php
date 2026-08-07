<?php

/**
 * Image Helper Functions
 *
 * Provides functions for optimized images with SEO features.
 */

/**
 * Generate meaningful alt text for images.
 *
 * @param string|null $type Image type (product, hero, gallery, avatar, farm, etc.)
 * @param string|null $context Additional context
 * @param string|null $src Image source for fallback
 * @return string Generated alt text
 */
function generate_image_alt(?string $type = null, ?string $context = null, ?string $src = null): string
{
    $siteName = config('seo.site_name', 'AbodaFarm');

    // Context-based alt text
    $contextAlt = [
        'product' => 'Product',
        'hero' => 'Hero Banner',
        'gallery' => 'Gallery',
        'avatar' => 'Photo',
        'farm' => 'Farm Profile',
        'farmer' => 'Farmer',
        'livestock' => 'Livestock',
        'testimonial' => 'Testimonial',
        'category' => 'Category',
        'logo' => 'Logo',
        'icon' => 'Icon',
        'banner' => 'Banner',
        'slide' => 'Slide',
        'thumbnail' => 'Thumbnail',
    ];

    // Product type alt text
    $productAlt = [
        'sapi' => 'Organic Cow',
        'cow' => 'Organic Cow',
        'kambing' => 'Healthy Goat',
        'goat' => 'Healthy Goat',
        'ayam' => 'Organic Chicken',
        'chicken' => 'Organic Chicken',
        'bebek' => 'Fresh Duck',
        'duck' => 'Fresh Duck',
        'daging' => 'Fresh Meat',
        'meat' => 'Fresh Meat',
        'sapi limousin' => 'Limousin Cow',
        'limousin' => 'Limousin Cow',
        'sapi brahman' => 'Brahman Cow',
        'brahman' => 'Brahman Cow',
        'kambing etawa' => 'Etawah Goat',
        'etawa' => 'Etawah Goat',
    ];

    // Generate alt based on type
    if ($type) {
        $typeLower = strtolower($type);

        // Check for product types
        foreach ($productAlt as $keyword => $alt) {
            if (str_contains($typeLower, $keyword)) {
                if ($context) {
                    return "{$alt} - {$context}";
                }
                return "{$alt} from {$siteName}";
            }
        }

        // Check for context-based
        foreach ($contextAlt as $key => $alt) {
            if (str_contains($typeLower, $key)) {
                return $alt;
            }
        }

        // Default type-based
        return ucfirst($type);
    }

    // Generate alt based on context
    if ($context) {
        $contextLower = strtolower($context);

        foreach ($contextAlt as $key => $alt) {
            if (str_contains($contextLower, $key)) {
                return "{$alt} - {$siteName}";
            }
        }

        return ucfirst($context);
    }

    // Generate alt from source filename
    if ($src) {
        $filename = pathinfo($src, PATHINFO_FILENAME);
        $filename = preg_replace('/[-_]/', ' ', $filename);
        $filename = preg_replace('/\d+/', '', $filename);
        $filename = trim(ucwords($filename));

        if (!empty($filename)) {
            return "{$filename} - {$siteName}";
        }
    }

    // Fallback
    return "{$siteName} Image";
}

/**
 * Generate WebP source URL for an image.
 *
 * @param string $src Original image URL
 * @return string WebP image URL
 */
function generate_webp_src(string $src): string
{
    // If already WebP, return as-is
    if (str_ends_with(strtolower($src), '.webp')) {
        return $src;
    }

    // Generate WebP path
    $baseUrl = rtrim(config('app.url', 'http://abodafarm.test'), '/');

    // For storage images, append .webp
    if (str_contains($src, '/storage/')) {
        return $baseUrl . $src . '.webp';
    }

    // For public images, try webp directory
    if (str_starts_with($src, '/')) {
        $parts = pathinfo($src);
        $dir = $parts['dirname'] ?? '/';
        $filename = $parts['filename'] ?? 'image';
        $extension = $parts['extension'] ?? 'jpg';

        // Check if webp version exists
        $webpPath = $dir . '/' . $filename . '.webp';

        // For now, return the original (in production, check if webp exists)
        return $baseUrl . $src;
    }

    return $src;
}

/**
 * Generate responsive image source URL.
 *
 * @param string $src Original image URL
 * @param string $size Size suffix (sm, md, lg, xl, 2xl)
 * @return string|null Responsive image URL or null
 */
function generate_responsive_src(string $src, string $size = 'md'): ?string
{
    $baseUrl = rtrim(config('app.url', 'http://abodafarm.test'), '/');

    // For storage images, generate responsive path
    if (str_contains($src, '/storage/')) {
        $parts = pathinfo($src);
        $filename = $parts['filename'] ?? 'image';
        $extension = $parts['extension'] ?? 'jpg';

        // Return responsive URL (assumes image service handles resizing)
        // In production, this would generate actual responsive URLs
        return $baseUrl . $src;
    }

    // For Unsplash images, use their sizing API
    if (str_contains($src, 'unsplash.com')) {
        $sizeMap = [
            'sm' => '640',
            'md' => '768',
            'lg' => '1024',
            'xl' => '1280',
            '2xl' => '1536',
        ];

        $width = $sizeMap[$size] ?? '768';

        // Add width parameter to Unsplash URL
        if (str_contains($src, '?')) {
            return $src . '&w=' . $width;
        } else {
            return $src . '?w=' . $width;
        }
    }

    // For other external images, return as-is
    return $baseUrl . $src;
}

/**
 * Get image dimensions from file or return defaults.
 *
 * @param string|null $src Image source
 * @param string $type Image type for default dimensions
 * @return array ['width' => int, 'height' => int]
 */
function get_image_dimensions(?string $src = null, string $type = 'thumbnail'): array
{
    // Default dimensions based on type
    $defaults = [
        'thumbnail' => ['width' => 200, 'height' => 200],
        'small' => ['width' => 300, 'height' => 300],
        'medium' => ['width' => 600, 'height' => 400],
        'large' => ['width' => 800, 'height' => 600],
        'hero' => ['width' => 1600, 'height' => 600],
        'banner' => ['width' => 1200, 'height' => 400],
        'avatar' => ['width' => 64, 'height' => 64],
        'logo' => ['width' => 200, 'height' => 60],
        'card' => ['width' => 400, 'height' => 300],
        'gallery' => ['width' => 800, 'height' => 600],
    ];

    return $defaults[$type] ?? $defaults['thumbnail'];
}

/**
 * Check if image exists or has valid source.
 *
 * @param string|null $src Image source
 * @return bool
 */
function image_exists(?string $src): bool
{
    if (empty($src)) {
        return false;
    }

    // External URLs are assumed to exist
    if (str_starts_with($src, 'http://') || str_starts_with($src, 'https://')) {
        return true;
    }

    // Local files
    $fullPath = public_path($src);
    return file_exists($fullPath);
}

/**
 * Get optimized image URL with CDN/optimization support.
 *
 * @param string|null $src Image source
 * @param array $options Optimization options
 * @return string Optimized image URL
 */
function optimized_image_url(?string $src, array $options = []): string
{
    if (empty($src)) {
        return config('seo.default_image', '/images/placeholder.jpg');
    }

    $baseUrl = rtrim(config('app.url', 'http://abodafarm.test'), '/');

    // Make absolute URL
    if (str_starts_with($src, '/')) {
        return $baseUrl . $src;
    }

    if (!str_contains($src, '://')) {
        return $baseUrl . '/' . $src;
    }

    return $src;
}
