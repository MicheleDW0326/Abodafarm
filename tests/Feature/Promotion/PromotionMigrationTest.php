<?php

namespace Tests\Feature\Promotion;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromotionMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_promotions_table_can_be_created(): void
    {
        $this->assertTrue(\Schema::hasTable('promotions'));
    }

    public function test_promotions_table_has_required_columns(): void
    {
        $columns = [
            'id',
            'name',
            'code',
            'description',
            'type',
            'value',
            'minimum_purchase',
            'maximum_discount',
            'usage_limit',
            'usage_per_user',
            'used_count',
            'starts_at',
            'ends_at',
            'status',
            'created_by',
            'created_at',
            'updated_at',
        ];

        foreach ($columns as $column) {
            $this->assertTrue(
                \Schema::hasColumn('promotions', $column),
                "Column {$column} does not exist in promotions table"
            );
        }
    }

    public function test_promotion_usages_table_can_be_created(): void
    {
        $this->assertTrue(\Schema::hasTable('promotion_usages'));
    }

    public function test_promotion_usages_table_has_required_columns(): void
    {
        $columns = [
            'id',
            'promotion_id',
            'user_id',
            'order_id',
            'discount_amount',
            'used_at',
            'created_at',
            'updated_at',
        ];

        foreach ($columns as $column) {
            $this->assertTrue(
                \Schema::hasColumn('promotion_usages', $column),
                "Column {$column} does not exist in promotion_usages table"
            );
        }
    }

    public function test_promotions_code_column_is_unique(): void
    {
        $this->artisan('migrate', ['--force' => true]);

        $admin = \App\Models\User::factory()->create();

        \App\Models\Promotion::create([
            'name' => 'Test Promo',
            'code' => 'UNIQUE_CODE',
            'type' => 'percentage',
            'value' => 10,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        \App\Models\Promotion::create([
            'name' => 'Duplicate Promo',
            'code' => 'UNIQUE_CODE',
            'type' => 'fixed',
            'value' => 50000,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'status' => 'active',
            'created_by' => $admin->id,
        ]);
    }

    public function test_promotions_type_is_enum(): void
    {
        $this->artisan('migrate', ['--force' => true]);

        $admin = \App\Models\User::factory()->create();

        $promotion = \App\Models\Promotion::create([
            'name' => 'Percentage Promo',
            'code' => 'PERCENT',
            'type' => 'percentage',
            'value' => 10,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        $this->assertEquals('percentage', $promotion->type);
    }

    public function test_promotions_status_is_enum(): void
    {
        $this->artisan('migrate', ['--force' => true]);

        $admin = \App\Models\User::factory()->create();

        $promotion = \App\Models\Promotion::create([
            'name' => 'Active Promo',
            'code' => 'ACTIVE',
            'type' => 'fixed',
            'value' => 50000,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        $this->assertEquals('active', $promotion->status);
    }
}
