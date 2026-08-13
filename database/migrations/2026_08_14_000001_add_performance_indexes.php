<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add performance indexes for frequently queried columns.
     * These indexes significantly improve query performance for:
     * - Product listing and filtering (Shop page)
     * - Search operations
     * - Dashboard queries
     * - Order processing
     */
    public function up(): void
    {
        // ============================================================
        // PRODUCTS TABLE - Most critical for Shop page performance
        // ============================================================

        // Single column indexes for common filters
        Schema::table('products', function (Blueprint $table) {
            // Status filters (available, pre_order, etc.)
            if (!Schema::hasIndex('products', 'idx_products_status')) {
                $table->index('status', 'idx_products_status');
            }

            // Stock availability check
            if (!Schema::hasIndex('products', 'idx_products_stock')) {
                $table->index('stock', 'idx_products_stock');
            }

            // Product type filter (live, fresh, supply)
            if (!Schema::hasIndex('products', 'idx_products_type')) {
                $table->index('type', 'idx_products_type');
            }

            // Featured products filter
            if (!Schema::hasIndex('products', 'idx_products_is_featured')) {
                $table->index('is_featured', 'idx_products_is_featured');
            }

            // Owner/farmer filter for product listing per farmer
            if (!Schema::hasIndex('products', 'idx_products_owner_id')) {
                $table->index('owner_id', 'idx_products_owner_id');
            }

            // Rating filter and sorting
            if (!Schema::hasIndex('products', 'idx_products_rating')) {
                $table->index('rating', 'idx_products_rating');
            }

            // Weight range filter for livestock
            if (!Schema::hasIndex('products', 'idx_products_weight')) {
                $table->index('weight', 'idx_products_weight');
            }

            // Created date for "newest" sorting
            if (!Schema::hasIndex('products', 'idx_products_created_at')) {
                $table->index('created_at', 'idx_products_created_at');
            }

            // Slug for SEO-friendly URLs
            if (!Schema::hasIndex('products', 'idx_products_slug')) {
                $table->index('slug', 'idx_products_slug');
            }
        });

        // Composite indexes for common query patterns
        Schema::table('products', function (Blueprint $table) {
            // Query: available() + inStock() - most common filter combination
            if (!Schema::hasIndex('products', 'idx_products_status_stock')) {
                $table->index(['status', 'stock'], 'idx_products_status_stock');
            }

            // Query: type filter + available + inStock
            if (!Schema::hasIndex('products', 'idx_products_type_status_stock')) {
                $table->index(['type', 'status', 'stock'], 'idx_products_type_status_stock');
            }

            // Query: category + available + inStock (Shop page with category filter)
            if (!Schema::hasIndex('products', 'idx_products_category_status_stock')) {
                $table->index(['category_id', 'status', 'stock'], 'idx_products_category_status_stock');
            }
        });

        // ============================================================
        // ORDERS TABLE - Dashboard and order management
        // ============================================================

        Schema::table('orders', function (Blueprint $table) {
            // User order history
            if (!Schema::hasIndex('orders', 'idx_orders_user_id')) {
                $table->index('user_id', 'idx_orders_user_id');
            }

            // Order type filter (cart vs inquiry/booking)
            if (!Schema::hasIndex('orders', 'idx_orders_order_type')) {
                $table->index('order_type', 'idx_orders_order_type');
            }

            // Placed date for monthly/yearly reports
            if (!Schema::hasIndex('orders', 'idx_orders_placed_at')) {
                $table->index('placed_at', 'idx_orders_placed_at');
            }

            // Staff assignment filter
            if (!Schema::hasIndex('orders', 'idx_orders_assigned_staff_id')) {
                $table->index('assigned_staff_id', 'idx_orders_assigned_staff_id');
            }
        });

        // Composite indexes for order queries
        Schema::table('orders', function (Blueprint $table) {
            // Query: status + placed_at (dashboard stats)
            if (!Schema::hasIndex('orders', 'idx_orders_status_placed_at')) {
                $table->index(['status', 'placed_at'], 'idx_orders_status_placed_at');
            }

            // Query: user_id + order_type (customer order history)
            if (!Schema::hasIndex('orders', 'idx_orders_user_order_type')) {
                $table->index(['user_id', 'order_type'], 'idx_orders_user_order_type');
            }
        });

        // ============================================================
        // ORDER ITEMS TABLE - Sales analytics
        // ============================================================

        Schema::table('order_items', function (Blueprint $table) {
            // Product sales aggregation
            if (!Schema::hasIndex('order_items', 'idx_order_items_product_id')) {
                $table->index('product_id', 'idx_order_items_product_id');
            }

            // Order items for specific order
            if (!Schema::hasIndex('order_items', 'idx_order_items_order_id')) {
                $table->index('order_id', 'idx_order_items_order_id');
            }
        });

        // ============================================================
        // REVIEWS TABLE - Rating and review queries
        // ============================================================

        Schema::table('reviews', function (Blueprint $table) {
            // Product reviews listing
            if (!Schema::hasIndex('reviews', 'idx_reviews_product_id')) {
                $table->index('product_id', 'idx_reviews_product_id');
            }

            // User reviews
            if (!Schema::hasIndex('reviews', 'idx_reviews_user_id')) {
                $table->index('user_id', 'idx_reviews_user_id');
            }

            // Status + created_at for moderation
            if (!Schema::hasIndex('reviews', 'idx_reviews_status_created')) {
                $table->index(['status', 'created_at'], 'idx_reviews_status_created');
            }
        });

        // ============================================================
        // ADDRESSES TABLE - Checkout
        // ============================================================

        Schema::table('addresses', function (Blueprint $table) {
            // User addresses listing
            if (!Schema::hasIndex('addresses', 'idx_addresses_user_id')) {
                $table->index('user_id', 'idx_addresses_user_id');
            }
        });

        // ============================================================
        // PRODUCT VIEWS TABLE - Recommendation engine
        // ============================================================

        Schema::table('product_views', function (Blueprint $table) {
            // Recently viewed products
            if (!Schema::hasIndex('product_views', 'idx_product_views_viewed_at')) {
                $table->index('viewed_at', 'idx_product_views_viewed_at');
            }

            // User's viewed products
            if (!Schema::hasIndex('product_views', 'idx_product_views_user_product')) {
                $table->index(['user_id', 'viewed_at'], 'idx_product_views_user_product');
            }
        });

        // ============================================================
        // CATEGORIES TABLE - Product filtering
        // ============================================================

        Schema::table('categories', function (Blueprint $table) {
            // Active categories
            if (!Schema::hasIndex('categories', 'idx_categories_is_active')) {
                $table->index('is_active', 'idx_categories_is_active');
            }

            // Category slug for URL
            if (!Schema::hasIndex('categories', 'idx_categories_slug')) {
                $table->index('slug', 'idx_categories_slug');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Products indexes
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_status');
            $table->dropIndex('idx_products_stock');
            $table->dropIndex('idx_products_type');
            $table->dropIndex('idx_products_is_featured');
            $table->dropIndex('idx_products_owner_id');
            $table->dropIndex('idx_products_rating');
            $table->dropIndex('idx_products_weight');
            $table->dropIndex('idx_products_created_at');
            $table->dropIndex('idx_products_slug');
            $table->dropIndex('idx_products_status_stock');
            $table->dropIndex('idx_products_type_status_stock');
            $table->dropIndex('idx_products_category_status_stock');
        });

        // Orders indexes
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_user_id');
            $table->dropIndex('idx_orders_order_type');
            $table->dropIndex('idx_orders_placed_at');
            $table->dropIndex('idx_orders_assigned_staff_id');
            $table->dropIndex('idx_orders_status_placed_at');
            $table->dropIndex('idx_orders_user_order_type');
        });

        // Order items indexes
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex('idx_order_items_product_id');
            $table->dropIndex('idx_order_items_order_id');
        });

        // Reviews indexes
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex('idx_reviews_product_id');
            $table->dropIndex('idx_reviews_user_id');
            $table->dropIndex('idx_reviews_status_created');
        });

        // Addresses indexes
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropIndex('idx_addresses_user_id');
        });

        // Product views indexes
        Schema::table('product_views', function (Blueprint $table) {
            $table->dropIndex('idx_product_views_viewed_at');
            $table->dropIndex('idx_product_views_user_product');
        });

        // Categories indexes
        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex('idx_categories_is_active');
            $table->dropIndex('idx_categories_slug');
        });
    }
};
