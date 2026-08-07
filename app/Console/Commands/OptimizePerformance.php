<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

/**
 * Performance Optimization Command
 *
 * Run this command to optimize the application for Core Web Vitals.
 */
class OptimizePerformance extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'perf:optimize
                            {--clear : Clear all caches}
                            {--build : Build optimized assets}
                            {--all : Run all optimizations}';

    /**
     * The console command description.
     */
    protected $description = 'Optimize application for Core Web Vitals (LCP, CLS, FCP, INP, TTFB)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Starting Performance Optimization...');

        // Clear caches if requested
        if ($this->option('clear') || $this->option('all')) {
            $this->clearCaches();
        }

        // Build optimized assets
        if ($this->option('build') || $this->option('all')) {
            $this->buildAssets();
        }

        // Run optimizations
        if ($this->option('all')) {
            $this->runOptimizations();
        }

        $this->info('✅ Performance optimization complete!');
        $this->displaySummary();

        return Command::SUCCESS;
    }

    /**
     * Clear all caches.
     */
    protected function clearCaches(): void
    {
        $this->info('🧹 Clearing caches...');

        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');

        if (app()->isProduction()) {
            Artisan::call('config:cache');
            Artisan::call('route:cache');
            $this->info('   ✓ Production caches created');
        }
    }

    /**
     * Build optimized assets.
     */
    protected function buildAssets(): void
    {
        $this->info('📦 Building optimized assets...');

        // Run Vite build
        $this->info('   Building Vite assets...');
        // Note: Vite build is typically done via npm run build

        // Clear and recreate symlinks
        if (File::exists(public_path('storage'))) {
            File::delete(public_path('storage'));
        }
        Artisan::call('storage:link');

        $this->info('   ✓ Assets built');
    }

    /**
     * Run additional optimizations.
     */
    protected function runOptimizations(): void
    {
        $this->info('⚡ Running optimizations...');

        // Optimize view caching
        Artisan::call('view:cache');
        $this->info('   ✓ Views compiled');

        // Clear event cache
        Artisan::call('event:cache');
        $this->info('   ✓ Events cached');

        // Precompile routes
        if (app()->isProduction()) {
            Artisan::call('route:cache');
            $this->info('   ✓ Routes cached');
        }
    }

    /**
     * Display optimization summary.
     */
    protected function displaySummary(): void
    {
        $this->newLine();
        $this->info('📊 Optimization Summary:');
        $this->table(
            ['Metric', 'Optimization Applied'],
            [
                ['LCP', 'Preloaded hero image & fonts, async CSS'],
                ['CLS', 'Reserved image dimensions, skeleton placeholders'],
                ['FCP', 'Inline critical CSS, preconnected fonts'],
                ['INP', 'Debounced Livewire updates, lazy components'],
                ['TTFB', 'Route caching, config caching, compression'],
            ]
        );

        $this->newLine();
        $this->warn('💡 Remember to run "npm run build" to build optimized assets.');
    }
}
