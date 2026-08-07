<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Performance Headers Middleware
 *
 * Adds cache control headers for static assets to improve TTFB
 * and enables other performance optimizations.
 */
class PerformanceHeaders
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only cache GET requests
        if ($request->isMethod('GET')) {
            $this->setCacheHeaders($response, $request);
            $this->setSecurityHeaders($response);
        }

        // Add compression hint
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        return $response;
    }

    /**
     * Set appropriate cache headers based on the request path.
     */
    protected function setCacheHeaders(Response $response, Request $request): void
    {
        $path = $request->path();
        $cacheConfig = config('performance.cache');

        // Determine cache duration based on path
        $cacheDuration = $this->getCacheDuration($path);

        if ($cacheDuration > 0) {
            // Public cache for static assets
            $response->headers->set(
                'Cache-Control',
                'public, max-age=' . $cacheDuration . ', s-maxage=' . ($cacheDuration * 2) . ', stale-while-revalidate=86400'
            );

            // Add ETag for better caching
            if (!$response->headers->has('ETag')) {
                $etag = md5($response->getContent());
                $response->setETag($etag);
            }
        } else {
            // No cache for dynamic content
            $response->headers->set(
                'Cache-Control',
                'no-cache, no-store, must-revalidate, private'
            );
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        }
    }

    /**
     * Get cache duration based on request path.
     */
    protected function getCacheDuration(string $path): int
    {
        $cache = config('performance.cache');

        // Immutable assets (versioned/hashed)
        if (preg_match('/\.(css|js)(\?v=|\?hash=)/', $path)) {
            return $cache['immutable'] ?? 31536000;
        }

        // Build assets (Vite hashed files)
        if (str_contains($path, '/build/')) {
            return $cache['immutable'] ?? 31536000;
        }

        // Static images
        if (str_contains($path, '/images/')) {
            return $cache['images'] ?? 604800;
        }

        // Fonts
        if (preg_match('/\.(woff2?|ttf|otf|eot)(\?|$)/', $path)) {
            return $cache['fonts'] ?? 2592000;
        }

        // Icons
        if (str_contains($path, '/icons/')) {
            return $cache['images'] ?? 604800;
        }

        // No cache for HTML, API, or dynamic routes
        return 0;
    }

    /**
     * Set security headers that also help with performance.
     */
    protected function setSecurityHeaders(Response $response): void
    {
        // Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Enable frame ancestors if needed
        // $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Referrer policy for privacy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions policy for browser features
        $response->headers->set(
            'Permissions-Policy',
            'camera=(), microphone=(), geolocation=(self), payment=()'
        );
    }
}
