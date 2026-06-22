<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get category IDs
        $sapi = Category::where('name', 'Sapi')->first();
        $kambing = Category::where('name', 'Kambing')->first();
        $ayam = Category::where('name', 'Ayam')->first();
        $bebek = Category::where('name', 'Bebek')->first();
        $daging = Category::where('name', 'Daging Segar')->first();

        // === TERNAK HIDUP (Live Livestock) ===

        $productsLive = [
            // Sapi
            [
                'category_id' => $sapi->id,
                'name' => 'Sapi Bali Jantan Premium',
                'description' => 'Sapi Bali jantan dengan postur ideal, cocok untuk aqiqah dan kurban. Dipelihara dengan pakan organik berkualitas.',
                'type' => 'live',
                'status' => 'available',
                'price' => 18500000,
                'weight' => 350,
                'age' => '2 Tahun',
                'sex' => 'male',
                'origin' => 'Talaud, Sulawesi Utara',
                'condition' => 'Sehat, Aktif',
                'stock' => 3,
                'image' => 'products/sapi-bali-premium.jpg',
                'is_featured' => true,
            ],
            [
                'category_id' => $sapi->id,
                'name' => 'Sapi Limousin Betina',
                'description' => 'Sapi Limousin betina produktif untuk breeding. Genetik unggul dari Belanda.',
                'type' => 'live',
                'status' => 'available',
                'price' => 19750000,
                'weight' => 380,
                'age' => '2.5 Tahun',
                'sex' => 'female',
                'origin' => 'Manado, Sulawesi Utara',
                'condition' => 'Sehat, Produktif',
                'stock' => 2,
                'image' => 'products/sapi-limousin.jpg',
                'is_featured' => false,
            ],
            [
                'category_id' => $sapi->id,
                'name' => 'Sapi Brangus Premium',
                'description' => 'Sapi Brangus persilangan Brahman dan Angus. Daging marbling tinggi, cocok untuk qurban.',
                'type' => 'live',
                'status' => 'available',
                'price' => 22500000,
                'weight' => 410,
                'age' => '3 Tahun',
                'sex' => 'male',
                'origin' => 'Talaud, Sulawesi Utara',
                'condition' => 'Sehat, Marbling Grade A',
                'stock' => 2,
                'image' => 'products/sapi-brangus.jpg',
                'is_featured' => true,
            ],
            [
                'category_id' => $sapi->id,
                'name' => 'Sapi Simental Warnanya',
                'description' => 'Sapi Simental dengan pertumbuhan cepat dan daging berkualitas. Cocok untuk penggemukan.',
                'type' => 'live',
                'status' => 'pre_order',
                'price' => 21000000,
                'weight' => 320,
                'age' => '1.8 Tahun',
                'sex' => 'male',
                'origin' => 'Minahasa, Sulawesi Utara',
                'condition' => 'Sehat, Siap Kurban',
                'stock' => 0,
                'image' => 'products/sapi-simental.jpg',
                'is_featured' => false,
            ],

            // Kambing
            [
                'category_id' => $kambing->id,
                'name' => 'Kambing Boer Unggulan',
                'description' => 'Kambing Boer murni untuk breeding dan qurban. Pertumbuhan cepat, daging tebal.',
                'type' => 'live',
                'status' => 'available',
                'price' => 4200000,
                'weight' => 48,
                'age' => '1.5 Tahun',
                'sex' => 'male',
                'origin' => 'Talaud, Sulawesi Utara',
                'condition' => 'Sehat, Vaksinasi Lengkap',
                'stock' => 5,
                'image' => 'products/kambing-boer.jpg',
                'is_featured' => true,
            ],
            [
                'category_id' => $kambing->id,
                'name' => 'Kambing Etawa Super',
                'description' => 'Kambing Etawa penghasil susu dan cempe berkualitas. Cocok untuk usaha peternakan.',
                'type' => 'live',
                'status' => 'available',
                'price' => 3500000,
                'weight' => 42,
                'age' => '2 Tahun',
                'sex' => 'female',
                'origin' => 'Tomohon, Sulawesi Utara',
                'condition' => 'Sehat, Produktif',
                'stock' => 4,
                'image' => 'products/kambing-etawa.jpg',
                'is_featured' => false,
            ],
            [
                'category_id' => $kambing->id,
                'name' => 'Kambing Kacang Mini',
                'description' => 'Kambing Kacang ukuran mini untuk aqiqah dan konsumsi. Mudah dipelihara.',
                'type' => 'live',
                'status' => 'in_care',
                'price' => 1800000,
                'weight' => 22,
                'age' => '1 Tahun',
                'sex' => 'mixed',
                'origin' => 'Bitung, Sulawesi Utara',
                'condition' => 'Dalam Perawatan',
                'stock' => 0,
                'image' => 'products/kambing-kacang.jpg',
                'is_featured' => false,
            ],

            // Ayam
            [
                'category_id' => $ayam->id,
                'name' => 'Ayam Organik Pedaging',
                'description' => 'Ayam pedaging organik tanpa hormon. Daging lebih keset dan flavours.',
                'type' => 'live',
                'status' => 'available',
                'price' => 185000,
                'weight' => 2.2,
                'age' => '12 Minggu',
                'sex' => 'mixed',
                'origin' => 'Bitung, Sulawesi Utara',
                'condition' => 'Sehat, Organik',
                'stock' => 150,
                'image' => 'products/ayam-organik.jpg',
                'is_featured' => true,
            ],
            [
                'category_id' => $ayam->id,
                'name' => 'Ayam Kampung Super',
                'description' => 'Ayam kampung dengan pertumbuhan lebih cepat. Telur dan daging berkualitas tinggi.',
                'type' => 'live',
                'status' => 'available',
                'price' => 125000,
                'weight' => 1.5,
                'age' => '4 Bulan',
                'sex' => 'mixed',
                'origin' => 'Minahasa, Sulawesi Utara',
                'condition' => 'Sehat, Bebas Antibiotik',
                'stock' => 80,
                'image' => 'products/ayam-kampung.jpg',
                'is_featured' => false,
            ],

            // Bebek
            [
                'category_id' => $bebek->id,
                'name' => 'Bebek Petelur Sehat',
                'description' => 'Bebek petelur Mozaik dan Peking. Produksi telur tinggi, doc优质.',
                'type' => 'live',
                'status' => 'in_care',
                'price' => 165000,
                'weight' => 1.8,
                'age' => '10 Minggu',
                'sex' => 'female',
                'origin' => 'Tomohon, Sulawesi Utara',
                'condition' => 'Dalam Perawatan',
                'stock' => 0,
                'image' => 'products/bebek-petelur.jpg',
                'is_featured' => false,
            ],
            [
                'category_id' => $bebek->id,
                'name' => 'Bebek Pedaging Alabio',
                'description' => 'Bebek pedaging Alabio dengan pertumbuhan cepat dan daging empuk.',
                'type' => 'live',
                'status' => 'available',
                'price' => 145000,
                'weight' => 2.0,
                'age' => '8 Minggu',
                'sex' => 'mixed',
                'origin' => 'Bitung, Sulawesi Utara',
                'condition' => 'Sehat, Siap Potong',
                'stock' => 60,
                'image' => 'products/bebek-alabio.jpg',
                'is_featured' => false,
            ],
        ];

        // === DAGING SEGAR (Fresh Meat) ===

        $productsFresh = [
            // Daging Sapi
            [
                'category_id' => $daging->id,
                'name' => 'Premium Beef Tenderloin',
                'description' => 'Potongan beef tenderloin premium, paling empuk dan cocok untuk steak.',
                'type' => 'fresh',
                'status' => 'available',
                'price' => 175000,
                'weight_per_unit' => 1.0,
                'stock' => 25,
                'image' => 'products/beef-tenderloin.jpg',
                'is_featured' => true,
            ],
            [
                'category_id' => $daging->id,
                'name' => 'Beef Ribeye Grade A',
                'description' => 'Ribeye dengan marbling sempurna, juicy dan penuh flavour.',
                'type' => 'fresh',
                'status' => 'available',
                'price' => 165000,
                'weight_per_unit' => 1.0,
                'stock' => 20,
                'image' => 'products/beef-ribeye.jpg',
                'is_featured' => true,
            ],
            [
                'category_id' => $daging->id,
                'name' => 'Beef Mince Premium',
                'description' => 'Daging giling premium tanpa campuran, cocok untuk bakso dan burger.',
                'type' => 'fresh',
                'status' => 'available',
                'price' => 95000,
                'weight_per_unit' => 0.5,
                'stock' => 40,
                'image' => 'products/beef-mince.jpg',
                'is_featured' => false,
            ],
            [
                'category_id' => $daging->id,
                'name' => 'Beef Steak Cut',
                'description' => 'Potongan beef steak siap masak dengan ketebalan ideal.',
                'type' => 'fresh',
                'status' => 'available',
                'price' => 125000,
                'weight_per_unit' => 0.25,
                'stock' => 35,
                'image' => 'products/beef-steak.jpg',
                'is_featured' => false,
            ],

            // Daging Kambing
            [
                'category_id' => $daging->id,
                'name' => 'Goat Roast Premium',
                'description' => 'Potongan daging kambing untuk roast dan gulai, empuk dan tidak prengus.',
                'type' => 'fresh',
                'status' => 'available',
                'price' => 135000,
                'weight_per_unit' => 0.5,
                'stock' => 15,
                'image' => 'products/goat-roast.jpg',
                'is_featured' => true,
            ],
            [
                'category_id' => $daging->id,
                'name' => 'Goat Stew Cubes',
                'description' => 'Potongan kambing untuk sup dan tongseng, sudah dibumbui tradisional.',
                'type' => 'fresh',
                'status' => 'available',
                'price' => 120000,
                'weight_per_unit' => 0.5,
                'stock' => 18,
                'image' => 'products/goat-stew.jpg',
                'is_featured' => false,
            ],

            // Daging Ayam
            [
                'category_id' => $daging->id,
                'name' => 'Organic Chicken Breast',
                'description' => 'Dada ayam organik tanpa hormon, rendah lemak tinggi protein.',
                'type' => 'fresh',
                'status' => 'available',
                'price' => 54000,
                'weight_per_unit' => 0.5,
                'stock' => 60,
                'image' => 'products/chicken-breast.jpg',
                'is_featured' => true,
            ],
            [
                'category_id' => $daging->id,
                'name' => 'Chicken Thighs Fresh',
                'description' => 'Paha ayam segar dengan kulit, cocok untuk goreng dan bakar.',
                'type' => 'fresh',
                'status' => 'available',
                'price' => 48000,
                'weight_per_unit' => 0.5,
                'stock' => 50,
                'image' => 'products/chicken-thighs.jpg',
                'is_featured' => false,
            ],
            [
                'category_id' => $daging->id,
                'name' => 'Whole Chicken Organic',
                'description' => 'Ayam utuh organik ±1.2kg, sudah dibersihkan siap masak.',
                'type' => 'fresh',
                'status' => 'available',
                'price' => 85000,
                'weight_per_unit' => 1.2,
                'stock' => 30,
                'image' => 'products/whole-chicken.jpg',
                'is_featured' => false,
            ],

            // Daging Bebek
            [
                'category_id' => $daging->id,
                'name' => 'Whole Duck Fresh',
                'description' => 'Bebek utuh segar ±1.5kg, cocok untuk resep tradisional.',
                'type' => 'fresh',
                'status' => 'available',
                'price' => 95000,
                'weight_per_unit' => 1.5,
                'stock' => 20,
                'image' => 'products/whole-duck.jpg',
                'is_featured' => true,
            ],
            [
                'category_id' => $daging->id,
                'name' => 'Duck Breast Premium',
                'description' => 'Dada bebek premium, daging tebal dan flavours untuk grill.',
                'type' => 'fresh',
                'status' => 'available',
                'price' => 75000,
                'weight_per_unit' => 0.4,
                'stock' => 25,
                'image' => 'products/duck-breast.jpg',
                'is_featured' => false,
            ],
        ];

        // Combine all products
        $allProducts = array_merge($productsLive, $productsFresh);

        foreach ($allProducts as $product) {
            Product::updateOrCreate(
                ['name' => $product['name']],
                $product
            );
        }
    }
}
