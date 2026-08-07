<?php

namespace Tests\Feature\Promotion;

use App\Models\Promotion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromotionSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed required data
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
    }

    public function test_seeder_creates_promotions(): void
    {
        $this->seed(\Database\Seeders\PromotionSeeder::class);

        $this->assertGreaterThanOrEqual(3, Promotion::count());
    }

    public function test_seeder_creates_welcome10_promotion(): void
    {
        $this->seed(\Database\Seeders\PromotionSeeder::class);

        $promo = Promotion::where('code', 'WELCOME10')->first();

        $this->assertNotNull($promo);
        $this->assertEquals('Diskon Selamat Datang', $promo->name);
        $this->assertEquals('percentage', $promo->type);
        $this->assertEquals(10, $promo->value);
        $this->assertEquals('active', $promo->status);
    }

    public function test_seeder_creates_hemat50000_promotion(): void
    {
        $this->seed(\Database\Seeders\PromotionSeeder::class);

        $promo = Promotion::where('code', 'HEMAT50000')->first();

        $this->assertNotNull($promo);
        $this->assertEquals('Hemat Rp 50.000', $promo->name);
        $this->assertEquals('fixed', $promo->type);
        $this->assertEquals(50000, $promo->value);
        $this->assertEquals('active', $promo->status);
    }

    public function test_seeder_creates_ongkgratis_promotion(): void
    {
        $this->seed(\Database\Seeders\PromotionSeeder::class);

        $promo = Promotion::where('code', 'ONGKIRGRATIS')->first();

        $this->assertNotNull($promo);
        $this->assertEquals('Gratis Ongkir', $promo->name);
        $this->assertEquals('free_shipping', $promo->type);
        $this->assertEquals('active', $promo->status);
    }

    public function test_seeder_can_be_rerun_without_duplicates(): void
    {
        $this->seed(\Database\Seeders\PromotionSeeder::class);
        $countAfterFirst = Promotion::count();

        $this->seed(\Database\Seeders\PromotionSeeder::class);
        $countAfterSecond = Promotion::count();

        $this->assertEquals($countAfterFirst, $countAfterSecond);
    }
}
