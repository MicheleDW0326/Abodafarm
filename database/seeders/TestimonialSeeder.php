<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $testimonials = [
            [
                'name' => 'Nadia Putri',
                'role' => 'Ibu Rumah Tangga',
                'content' => 'Sudah 3 kali pesan aqiqah di AbodaFarm. Ternaknya sehat, dagingnya segar, dan pelayanannya sangat memuaskan. Sangat direkomendasikan untuk keluarga yang butuh hewan aqiqah berkualitas!',
                'rating' => 5,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Rian Wijaya',
                'role' => 'Owner Restoran Seafood',
                'content' => 'Kami menjadi supplier tetap untuk daging bebek dan ayam organik. Kualitasnya konsisten dan harga kompetitif. AbodaFarm sudah partner terpercaya untuk bisnis kami.',
                'rating' => 5,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Salsa Amelia',
                'role' => 'Food Blogger',
                'content' => 'Daging organik dari AbodaFarm memang beda! Ayamnya lebih keset, bebeknya lebih flavorful. Saya selalu gunakan untuk resep-resep special saya. Terima kasih AbodaFarm!',
                'rating' => 5,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Bapak Herman',
                'role' => 'Peternak Kambing',
                'content' => 'Belanja kambing untuk kurban keluarga di AbodaFarm. Pilihannya banyak, harga transparan, dan ternak sampai dalam kondisi prima. Sangat puas dengan pelayanannya.',
                'rating' => 4,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Bu Santi',
                'role' => 'Pengusaha Catering',
                'content' => 'Sebagai catering, kualitas bahan baku adalah segalanya. AbodaFarm selalu deliver premium quality meat setiap kali order. Worth every rupiah!',
                'rating' => 5,
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($testimonials as $testimonial) {
            Testimonial::updateOrCreate(
                ['name' => $testimonial['name']],
                $testimonial
            );
        }
    }
}
