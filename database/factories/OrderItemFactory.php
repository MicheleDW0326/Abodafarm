<?php

namespace Database\Factories;

use App\Models\OrderItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(1, 5);
        $unitPrice = $this->faker->randomFloat(2, 50000, 5000000);
        $subtotal = $quantity * $unitPrice;

        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'product_name' => $this->faker->words(3, true),
            'product_image' => 'products/default.jpg',
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'subtotal' => $subtotal,
        ];
    }

    public function forOrder(Order $order): static
    {
        return $this->state(fn (array $attributes) => [
            'order_id' => $order->id,
        ]);
    }

    public function forProduct(Product $product): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'unit_price' => $product->price,
        ]);
    }
}
