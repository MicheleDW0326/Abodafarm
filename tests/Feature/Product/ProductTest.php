<?php

namespace Tests\Feature\Product;

use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected User $farmer;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        // Create farmer user
        $this->farmer = User::factory()->create([
            'role' => 'peternak',
        ]);

        // Create category
        $this->category = Category::factory()->create([
            'name' => 'Sapi',
            'slug' => 'sapi',
            'is_active' => true,
        ]);
    }

    /** @test */
    public function farmer_can_create_product(): void
    {
        $this->actingAs($this->farmer);

        $productData = [
            'name' => 'Sapi Limousin Premium',
            'category_id' => $this->category->id,
            'price' => 15000000,
            'type' => 'live',
            'status' => 'available',
            'stock' => 5,
            'weight' => 450.5,
            'age' => '2 tahun',
            'sex' => 'male',
            'breed' => 'Limousin',
        ];

        $response = $this->post(route('peternak.livestock.store'), $productData);

        $this->assertDatabaseHas('products', [
            'name' => 'Sapi Limousin Premium',
            'owner_id' => $this->farmer->id,
            'price' => 15000000,
        ]);
    }

    /** @test */
    public function product_name_is_required(): void
    {
        $this->actingAs($this->farmer);

        $response = $this->post(route('peternak.livestock.store'), [
            'category_id' => $this->category->id,
            'price' => 15000000,
        ]);

        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function product_price_must_be_numeric(): void
    {
        $this->actingAs($this->farmer);

        $response = $this->post(route('peternak.livestock.store'), [
            'name' => 'Sapi Test',
            'category_id' => $this->category->id,
            'price' => 'not-a-number',
        ]);

        $response->assertSessionHasErrors('price');
    }

    /** @test */
    public function product_can_be_updated(): void
    {
        $this->actingAs($this->farmer);

        $product = Product::factory()->create([
            'owner_id' => $this->farmer->id,
            'name' => 'Old Name',
            'price' => 10000000,
        ]);

        $response = $this->put(route('peternak.livestock.update', $product), [
            'name' => 'New Name',
            'price' => 12000000,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'New Name',
            'price' => 12000000,
        ]);
    }

    /** @test */
    public function product_can_be_deleted(): void
    {
        $this->actingAs($this->farmer);

        $product = Product::factory()->create([
            'owner_id' => $this->farmer->id,
        ]);

        $this->delete(route('peternak.livestock.destroy', $product));

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    /** @test */
    public function product_stock_can_be_updated(): void
    {
        $this->actingAs($this->farmer);

        $product = Product::factory()->create([
            'owner_id' => $this->farmer->id,
            'stock' => 10,
        ]);

        $response = $this->put(route('peternak.livestock.update', $product), [
            'name' => $product->name,
            'stock' => 5,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 5,
        ]);
    }

    /** @test */
    public function guest_cannot_access_product_creation(): void
    {
        $response = $this->get(route('peternak.livestock'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function customer_cannot_create_product(): void
    {
        $customer = User::factory()->create(['role' => 'pelanggan']);
        $this->actingAs($customer);

        $response = $this->get(route('peternak.livestock'));

        $response->assertForbidden();
    }

    /** @test */
    public function farmer_can_only_update_own_products(): void
    {
        $otherFarmer = User::factory()->create(['role' => 'peternak']);
        $product = Product::factory()->create([
            'owner_id' => $otherFarmer->id,
        ]);

        $this->actingAs($this->farmer);

        $response = $this->put(route('peternak.livestock.update', $product), [
            'name' => 'Hacked Name',
        ]);

        $response->assertForbidden();
    }

    /** @test */
    public function product_list_can_be_filtered_by_type(): void
    {
        Product::factory()->create(['type' => 'live', 'status' => 'available']);
        Product::factory()->create(['type' => 'fresh', 'status' => 'available']);
        Product::factory()->create(['type' => 'supply', 'status' => 'available']);

        $response = $this->get(route('shop') . '?type=live');

        $response->assertSee('live');
    }

    /** @test */
    public function product_detail_page_displays_all_info(): void
    {
        $product = Product::factory()->create([
            'name' => 'Sapi Limousin',
            'price' => 15000000,
            'type' => 'live',
        ]);

        $response = $this->get(route('shop.product', $product));

        $response->assertSee('Sapi Limousin');
        $response->assertSee('15000000');
    }
}
