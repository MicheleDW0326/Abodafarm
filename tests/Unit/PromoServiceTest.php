<?php

namespace Tests\Unit;

use App\Models\Promotion;
use App\Models\PromotionUsage;
use App\Models\User;
use App\Services\PromoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class PromoServiceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected PromoService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PromoService();
        $this->user = User::factory()->create();
    }

    protected function tearDown(): void
    {
        Session::forget('checkout.promo');
        parent::tearDown();
    }

    /** @test */
    public function it_validates_nonexistent_promo_code(): void
    {
        $cart = ['items' => [['price' => 100000, 'qty' => 1]]];

        $result = $this->service->validatePromo('INVALID', $cart, $this->user->id);

        $this->assertFalse($result['valid']);
        $this->assertEquals('not_found', $result['reason']);
    }

    /** @test */
    public function it_validates_inactive_promo(): void
    {
        Promotion::create([
            'name' => 'Inactive Promo',
            'code' => 'INACTIVE',
            'type' => 'percentage',
            'value' => 10,
            'status' => 'inactive',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
            'created_by' => $this->user->id,
        ]);

        $cart = ['items' => [['price' => 100000, 'qty' => 1]]];
        $result = $this->service->validatePromo('INACTIVE', $cart, $this->user->id);

        $this->assertFalse($result['valid']);
        $this->assertEquals('inactive', $result['reason']);
    }

    /** @test */
    public function it_validates_expired_promo(): void
    {
        Promotion::create([
            'name' => 'Expired Promo',
            'code' => 'EXPIRED',
            'type' => 'percentage',
            'value' => 10,
            'status' => 'active',
            'starts_at' => now()->subMonth(),
            'ends_at' => now()->subDay(),
            'created_by' => $this->user->id,
        ]);

        $cart = ['items' => [['price' => 100000, 'qty' => 1]]];
        $result = $this->service->validatePromo('EXPIRED', $cart, $this->user->id);

        $this->assertFalse($result['valid']);
        $this->assertEquals('expired', $result['reason']);
    }

    /** @test */
    public function it_validates_not_started_promo(): void
    {
        Promotion::create([
            'name' => 'Future Promo',
            'code' => 'FUTURE',
            'type' => 'percentage',
            'value' => 10,
            'status' => 'active',
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addMonth(),
            'created_by' => $this->user->id,
        ]);

        $cart = ['items' => [['price' => 100000, 'qty' => 1]]];
        $result = $this->service->validatePromo('FUTURE', $cart, $this->user->id);

        $this->assertFalse($result['valid']);
        $this->assertEquals('not_started', $result['reason']);
    }

    /** @test */
    public function it_validates_usage_limit_exceeded(): void
    {
        Promotion::create([
            'name' => 'Limited Promo',
            'code' => 'LIMITED',
            'type' => 'percentage',
            'value' => 10,
            'status' => 'active',
            'usage_limit' => 5,
            'used_count' => 5,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
            'created_by' => $this->user->id,
        ]);

        $cart = ['items' => [['price' => 100000, 'qty' => 1]]];
        $result = $this->service->validatePromo('LIMITED', $cart, $this->user->id);

        $this->assertFalse($result['valid']);
        $this->assertEquals('usage_limit_exceeded', $result['reason']);
    }

    /** @test */
    public function it_validates_per_user_limit(): void
    {
        $promo = Promotion::create([
            'name' => 'Per User Limit',
            'code' => 'PERUSER',
            'type' => 'percentage',
            'value' => 10,
            'status' => 'active',
            'usage_per_user' => 1,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
            'created_by' => $this->user->id,
        ]);

        // Create previous usage
        PromotionUsage::create([
            'promotion_id' => $promo->id,
            'user_id' => $this->user->id,
            'discount_amount' => 10000,
            'used_at' => now(),
        ]);

        $cart = ['items' => [['price' => 100000, 'qty' => 1]]];
        $result = $this->service->validatePromo('PERUSER', $cart, $this->user->id);

        $this->assertFalse($result['valid']);
        $this->assertEquals('per_user_limit_exceeded', $result['reason']);
    }

    /** @test */
    public function it_validates_minimum_purchase(): void
    {
        Promotion::create([
            'name' => 'Min Purchase',
            'code' => 'MINPURCHASE',
            'type' => 'percentage',
            'value' => 10,
            'minimum_purchase' => 200000,
            'status' => 'active',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
            'created_by' => $this->user->id,
        ]);

        // Cart is only 50000
        $cart = ['items' => [['price' => 50000, 'qty' => 1]]];
        $result = $this->service->validatePromo('MINPURCHASE', $cart, $this->user->id);

        $this->assertFalse($result['valid']);
        $this->assertEquals('minimum_not_met', $result['reason']);
    }

    /** @test */
    public function it_calculates_percentage_discount(): void
    {
        $promo = Promotion::create([
            'name' => '10% Off',
            'code' => 'PERCENT10',
            'type' => 'percentage',
            'value' => 10,
            'status' => 'active',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
            'created_by' => $this->user->id,
        ]);

        $cart = ['items' => [['price' => 100000, 'qty' => 2]]]; // 200000 total
        $result = $this->service->calculateDiscount($promo, $cart);

        $this->assertEquals(20000, $result['discount_amount']); // 10% of 200000
    }

    /** @test */
    public function it_caps_percentage_discount_at_maximum(): void
    {
        $promo = Promotion::create([
            'name' => '10% Off Max 15k',
            'code' => 'PERCENT10MAX',
            'type' => 'percentage',
            'value' => 10,
            'maximum_discount' => 15000,
            'status' => 'active',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
            'created_by' => $this->user->id,
        ]);

        $cart = ['items' => [['price' => 200000, 'qty' => 1]]]; // 200000 total
        $result = $this->service->calculateDiscount($promo, $cart);

        $this->assertEquals(15000, $result['discount_amount']); // capped at 15000
    }

    /** @test */
    public function it_calculates_fixed_discount(): void
    {
        $promo = Promotion::create([
            'name' => 'Fixed 50k',
            'code' => 'FIXED50',
            'type' => 'fixed',
            'value' => 50000,
            'status' => 'active',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
            'created_by' => $this->user->id,
        ]);

        $cart = ['items' => [['price' => 200000, 'qty' => 1]]];
        $result = $this->service->calculateDiscount($promo, $cart);

        $this->assertEquals(50000, $result['discount_amount']);
    }

    /** @test */
    public function it_caps_fixed_discount_at_subtotal(): void
    {
        $promo = Promotion::create([
            'name' => 'Fixed 100k',
            'code' => 'FIXED100',
            'type' => 'fixed',
            'value' => 100000,
            'status' => 'active',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
            'created_by' => $this->user->id,
        ]);

        $cart = ['items' => [['price' => 50000, 'qty' => 1]]]; // Only 50000
        $result = $this->service->calculateDiscount($promo, $cart);

        $this->assertEquals(50000, $result['discount_amount']); // capped at subtotal
    }

    /** @test */
    public function it_calculates_free_shipping_discount(): void
    {
        $promo = Promotion::create([
            'name' => 'Free Shipping',
            'code' => 'FREESHIP',
            'type' => 'free_shipping',
            'value' => 0,
            'status' => 'active',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
            'created_by' => $this->user->id,
        ]);

        $cart = ['items' => [['price' => 200000, 'qty' => 1]]];
        $result = $this->service->calculateDiscount($promo, $cart, 25000);

        $this->assertEquals(25000, $result['discount_amount']); // shipping cost
    }

    /** @test */
    public function it_applies_promo_successfully(): void
    {
        Promotion::create([
            'name' => '10% Off',
            'code' => 'APPLY10',
            'type' => 'percentage',
            'value' => 10,
            'status' => 'active',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
            'created_by' => $this->user->id,
        ]);

        $cart = ['items' => [['price' => 100000, 'qty' => 1]]];
        $result = $this->service->apply('APPLY10', $cart, $this->user->id);

        $this->assertTrue($result['valid']);
        $this->assertEquals(10000, $result['discount']);
        $this->assertEquals(90000, $result['grand_total']);
    }

    /** @test */
    public function it_removes_applied_promo(): void
    {
        Promotion::create([
            'name' => '10% Off',
            'code' => 'REMOVE10',
            'type' => 'percentage',
            'value' => 10,
            'status' => 'active',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
            'created_by' => $this->user->id,
        ]);

        $cart = ['items' => [['price' => 100000, 'qty' => 1]]];
        $this->service->apply('REMOVE10', $cart, $this->user->id);

        $this->assertTrue($this->service->hasApplied());

        $this->service->remove();

        $this->assertFalse($this->service->hasApplied());
        $this->assertNull($this->service->getApplied());
    }

    /** @test */
    public function it_validates_before_payment_and_rejects_invalid(): void
    {
        // Apply a promo first
        Promotion::create([
            'name' => '10% Off',
            'code' => 'VALIDATE10',
            'type' => 'percentage',
            'value' => 10,
            'status' => 'active',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
            'created_by' => $this->user->id,
        ]);

        $cart = ['items' => [['price' => 100000, 'qty' => 1]]];
        $this->service->apply('VALIDATE10', $cart, $this->user->id);

        // Now make the promo invalid
        Promotion::where('code', 'VALIDATE10')->update(['status' => 'inactive']);

        // Validate before payment should fail
        $result = $this->service->validateBeforePayment($cart, $this->user->id);

        $this->assertFalse($result['valid']);
        $this->assertFalse($this->service->hasApplied());
    }

    /** @test */
    public function it_marks_promo_as_used(): void
    {
        $promo = Promotion::create([
            'name' => '10% Off',
            'code' => 'MARKUSED',
            'type' => 'percentage',
            'value' => 10,
            'status' => 'active',
            'used_count' => 0,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
            'created_by' => $this->user->id,
        ]);

        $usage = $this->service->markAsUsed($promo->id, $this->user->id, null, 10000);

        $this->assertInstanceOf(PromotionUsage::class, $usage);
        $this->assertEquals($promo->id, $usage->promotion_id);
        $this->assertEquals($this->user->id, $usage->user_id);
        $this->assertEquals(10000, $usage->discount_amount);

        // Check usage count was incremented
        $promo->refresh();
        $this->assertEquals(1, $promo->used_count);
    }

    /** @test */
    public function it_calculates_final_total_correctly(): void
    {
        Promotion::create([
            'name' => '10% Off',
            'code' => 'FINAL10',
            'type' => 'percentage',
            'value' => 10,
            'status' => 'active',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
            'created_by' => $this->user->id,
        ]);

        $cart = ['items' => [['price' => 100000, 'qty' => 2]]]; // 200000
        $this->service->apply('FINAL10', $cart, $this->user->id);

        $totals = $this->service->getFinalTotal($cart, 25000);

        $this->assertEquals(200000, $totals['subtotal']);
        $this->assertEquals(20000, $totals['discount']); // 10%
        $this->assertEquals(25000, $totals['shipping']);
        $this->assertEquals(205000, $totals['grand_total']); // 200000 - 20000 + 25000
    }

    /** @test */
    public function it_removes_shipping_for_free_shipping_promo(): void
    {
        Promotion::create([
            'name' => 'Free Shipping',
            'code' => 'FREESHIPFINAL',
            'type' => 'free_shipping',
            'value' => 0,
            'status' => 'active',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
            'created_by' => $this->user->id,
        ]);

        $cart = ['items' => [['price' => 100000, 'qty' => 2]]]; // 200000
        $this->service->apply('FREESHIPFINAL', $cart, $this->user->id, 25000);

        $totals = $this->service->getFinalTotal($cart, 25000);

        $this->assertEquals(0, $totals['shipping']); // Shipping is free
        $this->assertEquals(200000, $totals['grand_total']); // No discount, no shipping
    }
}
