<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

test('product belongs to category', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);

    expect($product->category)->toBeInstanceOf(Category::class);
    expect($product->category->id)->toBe($category->id);
});

test('product belongs to owner', function () {
    $owner = User::factory()->create();
    $product = Product::factory()->create(['owner_id' => $owner->id]);

    expect($product->owner)->toBeInstanceOf(User::class);
    expect($product->owner->id)->toBe($owner->id);
});

test('product price is formatted correctly', function () {
    $product = Product::factory()->make(['price' => 15000000]);

    expect($product->formatted_price)->toBe('Rp 15.000.000');
});

test('product type label is correct', function () {
    $liveProduct = Product::factory()->make(['type' => 'live']);
    $freshProduct = Product::factory()->make(['type' => 'fresh']);
    $supplyProduct = Product::factory()->make(['type' => 'supply']);

    expect($liveProduct->type_label)->toBe('Ternak Hidup');
    expect($freshProduct->type_label)->toBe('Daging Segar');
    expect($supplyProduct->type_label)->toBe('Pakan & Perlengkapan');
});

test('product can check if available', function () {
    $available = Product::factory()->make(['status' => 'available']);
    $sold = Product::factory()->make(['status' => 'sold']);

    expect($available->isAvailable())->toBeTrue();
    expect($sold->isAvailable())->toBeFalse();
});

test('product can check if has stock', function () {
    $inStock = Product::factory()->make(['stock' => 10]);
    $outOfStock = Product::factory()->make(['stock' => 0]);

    expect($inStock->hasStock())->toBeTrue();
    expect($outOfStock->hasStock())->toBeFalse();
});

test('product has discount calculated correctly', function () {
    $productWithDiscount = Product::factory()->make([
        'price' => 100000,
        'discount_price' => 80000,
    ]);

    $productWithoutDiscount = Product::factory()->make([
        'price' => 100000,
        'discount_price' => null,
    ]);

    expect($productWithDiscount->has_discount)->toBeTrue();
    expect($productWithDiscount->discount_percentage)->toBe(20);
    expect($productWithoutDiscount->has_discount)->toBeFalse();
});

test('product current price uses discount when available', function () {
    $product = Product::factory()->make([
        'price' => 100000,
        'discount_price' => 80000,
    ]);

    expect($product->current_price)->toBe(80000.0);
});

test('product stock status is calculated', function () {
    $inStock = Product::factory()->make(['stock' => 10]);
    $lowStock = Product::factory()->make(['stock' => 3]);
    $outOfStock = Product::factory()->make(['stock' => 0]);

    expect($inStock->stock_status)->toBe('in_stock');
    expect($lowStock->stock_status)->toBe('low_stock');
    expect($outOfStock->stock_status)->toBe('out_of_stock');
});

test('product scope live filters correctly', function () {
    Product::factory()->create(['type' => 'live']);
    Product::factory()->create(['type' => 'fresh']);

    $liveProducts = Product::live()->get();

    expect($liveProducts)->toHaveCount(1);
    expect($liveProducts->first()->type)->toBe('live');
});

test('product scope available filters correctly', function () {
    Product::factory()->create(['status' => 'available']);
    Product::factory()->create(['status' => 'available']);
    Product::factory()->create(['status' => 'sold']);

    $availableProducts = Product::available()->get();

    expect($availableProducts)->toHaveCount(2);
});

test('product scope in stock filters correctly', function () {
    Product::factory()->create(['stock' => 10]);
    Product::factory()->create(['stock' => 0]);

    $inStockProducts = Product::inStock()->get();

    expect($inStockProducts)->toHaveCount(1);
});

test('product scope owned filters by user', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();

    Product::factory()->create(['owner_id' => $owner->id]);
    Product::factory()->create(['owner_id' => $owner->id]);
    Product::factory()->create(['owner_id' => $other->id]);

    $ownedProducts = Product::owned($owner->id)->get();

    expect($ownedProducts)->toHaveCount(2);
});
