<?php

namespace Database\Seeders;

use App\Models\ProductStock;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductStockSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();

        foreach ($products->take(3) as $product) {
            ProductStock::create([
                'product_id' => $product->id,
                'quantity' => rand(20, 50),
                'supplier' => $this->getRandomSupplier(),
                'date' => now()->subDays(rand(1, 30)),
            ]);

            ProductStock::create([
                'product_id' => $product->id,
                'quantity' => rand(15, 40),
                'supplier' => $this->getRandomSupplier(),
                'date' => now()->subDays(rand(1, 15)),
            ]);
        }
    }

    private function getRandomSupplier(): string
    {
        $suppliers = [
            'PT Ayam Jaya',
            'CV Ikan Laut',
            'Toko Segar Bersama',
            'Distributor Emas Tani',
            'Koperasi Tani Sejahtera',
        ];

        return $suppliers[array_rand($suppliers)];
    }
}
