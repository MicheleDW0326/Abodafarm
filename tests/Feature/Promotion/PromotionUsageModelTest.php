<?php

namespace Tests\Feature\Promotion;

use App\Models\Order;
use App\Models\Promotion;
use App\Models\PromotionUsage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromotionUsageModelTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $user;
    protected Promotion $promotion;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->user = User::factory()->create();

        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);

        $this->admin->assignRole('admin');

        $this->promotion = Promotion::create([
            'name' => 'Test Promo',
            'code' => 'TESTPROMO',
            'type' => 'percentage',
            'value' => 10,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'status' => 'active',
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_promotion_usage_can_be_created(): void
    {
        $usage = PromotionUsage::create([
            'promotion_id' => $this->promotion->id,
            'user_id' => $this->user->id,
            'discount_amount' => 15000,
            'used_at' => now(),
        ]);

        $this->assertDatabaseHas('promotion_usages', [
            'promotion_id' => $this->promotion->id,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_promotion_usage_has_promotion_relationship(): void
    {
        $usage = PromotionUsage::create([
            'promotion_id' => $this->promotion->id,
            'user_id' => $this->user->id,
            'discount_amount' => 15000,
            'used_at' => now(),
        ]);

        $this->assertNotNull($usage->promotion);
        $this->assertEquals($this->promotion->id, $usage->promotion->id);
    }

    public function test_promotion_usage_has_user_relationship(): void
    {
        $usage = PromotionUsage::create([
            'promotion_id' => $this->promotion->id,
            'user_id' => $this->user->id,
            'discount_amount' => 15000,
            'used_at' => now(),
        ]);

        $this->assertNotNull($usage->user);
        $this->assertEquals($this->user->id, $usage->user->id);
    }

    public function test_promotion_usage_can_have_null_order(): void
    {
        $usage = PromotionUsage::create([
            'promotion_id' => $this->promotion->id,
            'user_id' => $this->user->id,
            'order_id' => null,
            'discount_amount' => 15000,
            'used_at' => now(),
        ]);

        $this->assertNull($usage->order);
    }

    public function test_promotion_usage_by_user_scope(): void
    {
        $anotherUser = User::factory()->create();

        PromotionUsage::create([
            'promotion_id' => $this->promotion->id,
            'user_id' => $this->user->id,
            'discount_amount' => 15000,
            'used_at' => now(),
        ]);

        PromotionUsage::create([
            'promotion_id' => $this->promotion->id,
            'user_id' => $anotherUser->id,
            'discount_amount' => 20000,
            'used_at' => now(),
        ]);

        $userUsages = PromotionUsage::byUser($this->user->id)->get();

        $this->assertCount(1, $userUsages);
        $this->assertEquals($this->user->id, $userUsages->first()->user_id);
    }

    public function test_promotion_usage_by_promotion_scope(): void
    {
        $anotherPromo = Promotion::create([
            'name' => 'Another Promo',
            'code' => 'ANOTHER',
            'type' => 'fixed',
            'value' => 50000,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'status' => 'active',
            'created_by' => $this->admin->id,
        ]);

        PromotionUsage::create([
            'promotion_id' => $this->promotion->id,
            'user_id' => $this->user->id,
            'discount_amount' => 15000,
            'used_at' => now(),
        ]);

        PromotionUsage::create([
            'promotion_id' => $anotherPromo->id,
            'user_id' => $this->user->id,
            'discount_amount' => 50000,
            'used_at' => now(),
        ]);

        $promoUsages = PromotionUsage::byPromotion($this->promotion->id)->get();

        $this->assertCount(1, $promoUsages);
        $this->assertEquals($this->promotion->id, $promoUsages->first()->promotion_id);
    }

    public function test_promotion_usage_belongs_to_order(): void
    {
        $order = Order::create([
            'user_id' => $this->user->id,
            'order_number' => 'ORD-TEST-001',
            'status' => 'paid',
            'total_amount' => 150000,
            'payment_status' => 'paid',
        ]);

        $usage = PromotionUsage::create([
            'promotion_id' => $this->promotion->id,
            'user_id' => $this->user->id,
            'order_id' => $order->id,
            'discount_amount' => 15000,
            'used_at' => now(),
        ]);

        $this->assertNotNull($usage->order);
        $this->assertEquals($order->id, $usage->order->id);
    }
}
