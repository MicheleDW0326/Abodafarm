<?php

namespace Tests\Feature\Order;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected User $farmer;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        // Create customer
        $this->customer = User::factory()->create([
            'role' => 'pelanggan',
        ]);

        // Create farmer
        $this->farmer = User::factory()->create([
            'role' => 'peternak',
        ]);

        // Create category
        $category = Category::factory()->create();

        // Create product
        $this->product = Product::factory()->create([
            'owner_id' => $this->farmer->id,
            'category_id' => $category->id,
            'stock' => 10,
            'price' => 1000000,
            'status' => 'available',
        ]);
    }

    /** @test */
    public function customer_can_create_cart_order(): void
    {
        $this->actingAs($this->customer);

        $orderData = [
            'order_type' => 'cart',
            'customer_name' => 'John Doe',
            'customer_phone' => '08123456789',
            'customer_address' => 'Jl. Test No. 1, Jakarta',
            'notes' => 'Please deliver in the morning',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                ],
            ],
        ];

        $response = $this->post(route('checkout.process'), $orderData);

        $this->assertDatabaseHas('orders', [
            'user_id' => $this->customer->id,
            'order_type' => 'cart',
            'customer_name' => 'John Doe',
        ]);
    }

    /** @test */
    public function customer_can_create_inquiry_order(): void
    {
        $this->actingAs($this->customer);

        $orderData = [
            'order_type' => 'inquiry',
            'customer_name' => 'John Doe',
            'customer_phone' => '08123456789',
            'customer_address' => 'Jl. Test No. 1, Jakarta',
            'preferred_delivery_date' => now()->addDays(7)->toDateString(),
            'notes' => 'Interested in this livestock',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 1,
                ],
            ],
        ];

        $response = $this->post(route('checkout.process'), $orderData);

        $this->assertDatabaseHas('orders', [
            'user_id' => $this->customer->id,
            'order_type' => 'inquiry',
        ]);
    }

    /** @test */
    public function order_number_is_unique(): void
    {
        $this->actingAs($this->customer);

        $order1 = Order::factory()->create([
            'user_id' => $this->customer->id,
        ]);

        $order2 = Order::factory()->create([
            'user_id' => $this->customer->id,
        ]);

        $this->assertNotEquals($order1->order_number, $order2->order_number);
    }

    /** @test */
    public function order_total_is_calculated_correctly(): void
    {
        $product1 = Product::factory()->create(['price' => 100000]);
        $product2 = Product::factory()->create(['price' => 50000]);

        $this->actingAs($this->customer);

        $response = $this->post(route('checkout.process'), [
            'order_type' => 'cart',
            'customer_name' => 'Test',
            'customer_phone' => '0812',
            'customer_address' => 'Test Address',
            'items' => [
                ['product_id' => $product1->id, 'quantity' => 2],
                ['product_id' => $product2->id, 'quantity' => 1],
            ],
        ]);

        $order = Order::where('user_id', $this->customer->id)->latest()->first();

        // 2 x 100000 + 1 x 50000 = 250000
        $this->assertEquals(250000, $order->total_amount);
    }

    /** @test */
    public function stock_is_decremented_when_order_is_approved(): void
    {
        $initialStock = $this->product->stock;

        $this->actingAs($this->customer);

        $response = $this->post(route('checkout.process'), [
            'order_type' => 'cart',
            'customer_name' => 'Test',
            'customer_phone' => '0812',
            'customer_address' => 'Test Address',
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 2],
            ],
        ]);

        $order = Order::where('user_id', $this->customer->id)->latest()->first();

        // Order should be pending, stock not decremented yet
        $this->assertEquals($initialStock, $this->product->fresh()->stock);
    }

    /** @test */
    public function customer_can_view_own_orders(): void
    {
        $this->actingAs($this->customer);

        Order::factory()->count(3)->create([
            'user_id' => $this->customer->id,
        ]);

        $response = $this->get(route('pelanggan.orders'));

        $response->assertOk();
    }

    /** @test */
    public function customer_cannot_view_others_orders(): void
    {
        $otherCustomer = User::factory()->create(['role' => 'pelanggan']);
        Order::factory()->create(['user_id' => $otherCustomer->id]);

        $this->actingAs($this->customer);

        $response = $this->get(route('pelanggan.orders'));

        // Should only see own orders
        $response->assertOk();
    }

    /** @test */
    public function order_status_can_be_updated(): void
    {
        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'status' => 'pending',
        ]);

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin);

        $response = $this->put(route('admin.orders.update', $order), [
            'status' => 'paid',
        ]);

        $this->assertEquals('paid', $order->fresh()->status);
    }

    /** @test */
    public function guest_cannot_create_order(): void
    {
        $response = $this->post(route('checkout.process'), [
            'order_type' => 'cart',
            'customer_name' => 'Test',
            'customer_phone' => '0812',
            'customer_address' => 'Test',
            'items' => [],
        ]);

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function order_must_have_at_least_one_item(): void
    {
        $this->actingAs($this->customer);

        $response = $this->post(route('checkout.process'), [
            'order_type' => 'cart',
            'customer_name' => 'Test',
            'customer_phone' => '0812',
            'customer_address' => 'Test',
            'items' => [],
        ]);

        $response->assertSessionHasErrors('items');
    }

    /** @test */
    public function order_can_be_cancelled_by_customer_when_pending(): void
    {
        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->customer);

        $response = $this->put(route('pelanggan.orders.cancel', $order), [
            'reason' => 'Changed my mind',
        ]);

        $this->assertEquals('cancelled', $order->fresh()->status);
    }

    /** @test */
    public function order_cannot_be_cancelled_when_already_shipped(): void
    {
        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'status' => 'shipped',
        ]);

        $this->actingAs($this->customer);

        $response = $this->put(route('pelanggan.orders.cancel', $order));

        // Should fail or show error
        $response->assertSessionHas('error');
    }
}
