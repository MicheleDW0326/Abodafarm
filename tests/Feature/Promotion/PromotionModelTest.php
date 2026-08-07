<?php

namespace Tests\Feature\Promotion;

use App\Models\Promotion;
use App\Models\PromotionUsage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromotionModelTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->user = User::factory()->create();

        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);

        $this->admin->assignRole('admin');
    }

    public function test_promotion_can_be_created(): void
    {
        $promotion = Promotion::create([
            'name' => 'Test Promo',
            'code' => 'TEST123',
            'description' => 'Test description',
            'type' => 'percentage',
            'value' => 15,
            'minimum_purchase' => 100000,
            'maximum_discount' => 25000,
            'usage_limit' => 100,
            'usage_per_user' => 2,
            'used_count' => 0,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'status' => 'active',
            'created_by' => $this->admin->id,
        ]);

        $this->assertDatabaseHas('promotions', [
            'code' => 'TEST123',
            'name' => 'Test Promo',
        ]);
    }

    public function test_promotion_has_creator_relationship(): void
    {
        $promotion = Promotion::create([
            'name' => 'Creator Test',
            'code' => 'CREATOR',
            'type' => 'fixed',
            'value' => 50000,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'status' => 'active',
            'created_by' => $this->admin->id,
        ]);

        $this->assertNotNull($promotion->creator);
        $this->assertEquals($this->admin->id, $promotion->creator->id);
    }

    public function test_promotion_has_usages_relationship(): void
    {
        $promotion = Promotion::create([
            'name' => 'Usage Test',
            'code' => 'USAGE',
            'type' => 'percentage',
            'value' => 10,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'status' => 'active',
            'created_by' => $this->admin->id,
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $promotion->usages);
    }

    public function test_promotion_active_scope(): void
    {
        Promotion::create([
            'name' => 'Active Promo',
            'code' => 'ACTIVE1',
            'type' => 'percentage',
            'value' => 10,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'status' => 'active',
            'created_by' => $this->admin->id,
        ]);

        Promotion::create([
            'name' => 'Inactive Promo',
            'code' => 'INACTIVE1',
            'type' => 'percentage',
            'value' => 10,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'status' => 'inactive',
            'created_by' => $this->admin->id,
        ]);

        $activePromotions = Promotion::active()->get();

        $this->assertCount(1, $activePromotions);
        $this->assertEquals('ACTIVE1', $activePromotions->first()->code);
    }

    public function test_promotion_valid_scope(): void
    {
        Promotion::create([
            'name' => 'Valid Promo',
            'code' => 'VALID1',
            'type' => 'percentage',
            'value' => 10,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
            'status' => 'active',
            'created_by' => $this->admin->id,
        ]);

        Promotion::create([
            'name' => 'Expired Promo',
            'code' => 'EXPIRED1',
            'type' => 'percentage',
            'value' => 10,
            'starts_at' => now()->subMonth(),
            'ends_at' => now()->subDay(),
            'status' => 'active',
            'created_by' => $this->admin->id,
        ]);

        Promotion::create([
            'name' => 'Future Promo',
            'code' => 'FUTURE1',
            'type' => 'percentage',
            'value' => 10,
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addMonth(),
            'status' => 'active',
            'created_by' => $this->admin->id,
        ]);

        $validPromotions = Promotion::valid()->get();

        $this->assertCount(1, $validPromotions);
        $this->assertEquals('VALID1', $validPromotions->first()->code);
    }

    public function test_promotion_is_active_method(): void
    {
        $activePromo = Promotion::create([
            'name' => 'Active',
            'code' => 'ISMETHOD1',
            'type' => 'percentage',
            'value' => 10,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'status' => 'active',
            'created_by' => $this->admin->id,
        ]);

        $inactivePromo = Promotion::create([
            'name' => 'Inactive',
            'code' => 'ISMETHOD2',
            'type' => 'percentage',
            'value' => 10,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'status' => 'inactive',
            'created_by' => $this->admin->id,
        ]);

        $this->assertTrue($activePromo->isActive());
        $this->assertFalse($inactivePromo->isActive());
    }

    public function test_promotion_has_usages_remaining_method(): void
    {
        $unlimitedPromo = Promotion::create([
            'name' => 'Unlimited',
            'code' => 'UNLIMITED1',
            'type' => 'percentage',
            'value' => 10,
            'usage_limit' => null,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'status' => 'active',
            'created_by' => $this->admin->id,
        ]);

        $limitedPromo = Promotion::create([
            'name' => 'Limited',
            'code' => 'LIMITED1',
            'type' => 'percentage',
            'value' => 10,
            'usage_limit' => 10,
            'used_count' => 5,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'status' => 'active',
            'created_by' => $this->admin->id,
        ]);

        $exhaustedPromo = Promotion::create([
            'name' => 'Exhausted',
            'code' => 'EXHAUSTED1',
            'type' => 'percentage',
            'value' => 10,
            'usage_limit' => 10,
            'used_count' => 10,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'status' => 'active',
            'created_by' => $this->admin->id,
        ]);

        $this->assertTrue($unlimitedPromo->hasUsagesRemaining());
        $this->assertTrue($limitedPromo->hasUsagesRemaining());
        $this->assertFalse($exhaustedPromo->hasUsagesRemaining());
    }

    public function test_promotion_increment_usage_method(): void
    {
        $promo = Promotion::create([
            'name' => 'Increment Test',
            'code' => 'INCREMENT1',
            'type' => 'percentage',
            'value' => 10,
            'used_count' => 5,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'status' => 'active',
            'created_by' => $this->admin->id,
        ]);

        $this->assertEquals(5, $promo->used_count);

        $promo->incrementUsage();

        $this->assertEquals(6, $promo->fresh()->used_count);
    }
}
