<?php

namespace App\Providers;

use App\Services\SeoService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

/**
 * SEO Service Provider
 *
 * Registers the SEO service and Blade directives.
 */
class SeoServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register SeoService as a singleton
        $this->app->singleton(SeoService::class, function () {
            return new SeoService();
        });

        // Merge SEO config
        $this->mergeConfigFrom(
            config_path('seo.php'),
            'seo'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerBladeDirectives();
    }

    /**
     * Register Blade directives for SEO.
     */
    protected function registerBladeDirectives(): void
    {
        // @seo directive - Set SEO data for the page
        Blade::directive('seo', function (mixed $expression) {
            // If empty, just render default SEO tags
            if (empty($expression)) {
                return '{!! seo_tags() !!}';
            }

            // Parse the expression as array or string
            return '<?php
                $seoData = ' . $expression . ';
                if (is_array($seoData)) {
                    seo()->set($seoData);
                } elseif (is_string($seoData)) {
                    seo()->title($seoData);
                }
            ?>';
        });

        // @seoPublic directive - Set page as public (index, follow)
        Blade::directive('seoPublic', function () {
            return '<?php seo()->publicPage(); ?>';
        });

        // @seoPrivate directive - Set page as private (noindex, nofollow)
        Blade::directive('seoPrivate', function () {
            return '<?php seo()->privatePage(); ?>';
        });

        // @seoTitle directive - Set page title
        Blade::directive('seoTitle', function (string $title) {
            return '<?php seo()->title(' . $title . '); ?>';
        });

        // @seoDescription directive - Set meta description
        Blade::directive('seoDescription', function (string $description) {
            return '<?php seo()->description(' . $description . '); ?>';
        });

        // @seoImage directive - Set Open Graph image
        Blade::directive('seoImage', function (string $image) {
            return '<?php seo()->image(' . $image . '); ?>';
        });
    }
}
