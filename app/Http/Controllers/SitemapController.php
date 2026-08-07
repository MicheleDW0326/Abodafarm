<?php

namespace App\Http\Controllers;

use App\Services\SitemapService;
use Illuminate\Http\Response;

/**
 * Sitemap Controller
 *
 * Generates XML sitemap for search engines.
 */
class SitemapController extends Controller
{
    protected SitemapService $sitemapService;

    public function __construct(SitemapService $sitemapService)
    {
        $this->sitemapService = $sitemapService;
    }

    /**
     * Generate and serve the XML sitemap.
     */
    public function index(): Response
    {
        $content = $this->sitemapService->generate();

        return response($content, 200, [
            'Content-Type' => 'application/xml',
            'Content-Length' => strlen($content),
            'Cache-Control' => 'public, max-age=86400', // Cache for 1 day
        ]);
    }

    /**
     * Get sitemap statistics.
     */
    public function stats(): array
    {
        $this->sitemapService->generate(); // Initialize items

        return [
            'total_urls' => $this->sitemapService->getItemsCount(),
            'base_url' => $this->sitemapService->getBaseUrl(),
            'generated_at' => now()->toIso8601String(),
        ];
    }
}
