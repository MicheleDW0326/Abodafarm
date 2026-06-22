<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faqs = [
            // Kategori: Produk
            [
                'question' => 'Apakah semua ternak sudah divaksinasi?',
                'answer' => 'Ya, semua ternak di AbodaFarm sudah mendapatkan vaksinasi lengkap sesuai jadwal. Kami melakukan vaksinasi rutin untuk mencegah penyakit seperti Anthrax, SE, dan penyakit lainnya. Sertifikat vaksinasi dapat disediakan atas permintaan.',
                'category' => 'product',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'question' => 'Bagaimana cara memilih ternak yang tepat untuk aqiqah?',
                'answer' => 'Untuk aqiqah, kami merekomendasikan memilih ternak yang sehat, tidak cacat, dan sesuai dengan syariat. Sapi bisa dipilih dengan berat 200-300kg untuk 7 orang, dan kambing untuk 1-2 orang. Tim kami siap membantu memilihkan ternak yang tepat.',
                'category' => 'product',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'question' => 'Apakah bisa lihat kondisi ternak sebelum membeli?',
                'answer' => 'Tentu! Kami mengundang calon pembeli untuk visitasi ke farm dan melihat langsung kondisi ternak. Foto dan video detail juga tersedia di website kami untuk setiap produk.',
                'category' => 'product',
                'is_active' => true,
                'sort_order' => 3,
            ],

            // Kategori: Pemesanan
            [
                'question' => 'Bagaimana cara melakukan pemesanan?',
                'answer' => 'Untuk daging segar, Anda bisa langsung add to cart dan checkout di website. Untuk ternak hidup, silakan hubungi kami via WhatsApp untuk diskusi lebih lanjut mengenai pilihan ternak, jadwal pengambilan, dan detail lainnya.',
                'category' => 'order',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'question' => 'Berapa lama proses pengiriman?',
                'answer' => 'Pengiriman untuk daging segar biasanya 1-2 hari kerja setelah pembayaran terkonfirmasi. Untuk ternak hidup, kami atur jadwal pengambilan/pengiriman sesuai kesepakatan. Area Sulawesi Utara dan sekitarnya dilayani pengiriman gratis.',
                'category' => 'order',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'question' => 'Apakah bisa pre-order untukqurban?',
                'answer' => 'Ya, kami menerima pre-order untukqurban sejak 2 bulan sebelum hari raya. Pre-order akan mendapat prioritas انتخاب terbaik dan harga khusus. Hubungi kami lebih awal untuk mendapatkan pilihan terbaik.',
                'category' => 'order',
                'is_active' => true,
                'sort_order' => 6,
            ],

            // Kategori: Pengiriman
            [
                'question' => 'Wilayah apa saja yang dilayani pengiriman?',
                'answer' => 'Kami melayani pengiriman ke seluruh Sulawesi Utara termasuk Manado, Bitung, Tomohon, Minahasa, Talaud, dan sekitarnya. Untuk wilayah lain, silakan hubungi kami untuk konfirmasi.',
                'category' => 'delivery',
                'is_active' => true,
                'sort_order' => 7,
            ],
            [
                'question' => 'Bagaimana jika ternak mati saat perjalanan?',
                'answer' => 'Kami memberikan garansi 100% untuk kondisi ternak saat diterima. Jika ada masalah, kami akan mengganti dengan ternak sejenis atau refund penuh. Semua pengiriman dilengkapi asuransi dan dokumentasi kondisi.',
                'category' => 'delivery',
                'is_active' => true,
                'sort_order' => 8,
            ],

            // Kategori: Pembayaran
            [
                'question' => 'Metode pembayaran apa saja yang tersedia?',
                'answer' => 'Kami menerima pembayaran via Transfer Bank (BCA, Mandiri, BRI), e-wallet (OVO, Dana, GoPay), dan现金 untuk pengambilan langsung di farm. Pembayaran online akan mendapat konfirmasi via SMS/WhatsApp.',
                'category' => 'payment',
                'is_active' => true,
                'sort_order' => 9,
            ],
            [
                'question' => 'Apakah ada garansi uang kembali?',
                'answer' => 'Ya, kami memberikan garansi kepuasan. Jika produk tidak sesuai ekspektasi, silakan hubungi kami dalam 24 jam setelah penerimaan untuk komplain. Kami akan berikan solusi terbaik untuk setiap kasus.',
                'category' => 'payment',
                'is_active' => true,
                'sort_order' => 10,
            ],
        ];

        foreach ($faqs as $faq) {
            Faq::updateOrCreate(
                ['question' => $faq['question']],
                $faq
            );
        }
    }
}
