<?php

namespace App\Exports;

class ProductImportTemplate
{
    public function download()
    {
        $headers = [
            'Nama Produk',
            'Kategori',
            'Tipe Produk',
            'Harga',
            'Stok',
            'Deskripsi',
            'Ras',
            'Berat (kg)',
            'Umur',
            'Jenis Kelamin',
            'Asal',
            'Kondisi',
            'Lokasi Kandang',
            'Berat Per Unit (kg)',
            'Unit',
            'Harga Diskon',
            'Diskon (%)',
            'Spesifikasi',
            'Cara Penggunaan',
            'Info Pengiriman',
            'Unggulan',
        ];

        $sampleRows = [
            [
                'Sapi Limousin Betina',
                'Sapi',
                'live',
                15000000,
                5,
                'Sapi limousin betina usia 2 tahun kondisi sehat siap kawin',
                'Limousin',
                350.5,
                '2 Tahun',
                'female',
                'Jawa Timur',
                'Sehat',
                'Kandang A',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                0,
            ],
            [
                'Pakan Ayam Starter 25kg',
                'Pakan Ternak',
                'supply',
                450000,
                100,
                'Pakan ayam starter untuk anak ayam umur 1-3 minggu',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                'kg',
                450000,
                10,
                'Komposisi: Jagung 50%, Dedak 20%, Bungkil 30%',
                'Campurkan 100g per liter air minum',
                'Gratis ongkir untuk pembelian min 10 sack',
                0,
            ],
            [
                'Vitamin B-Complex',
                'Vitamin & Suplemen',
                'supply',
                75000,
                500,
                'Vitamin B kompleks untuk meningkatkan nafsu makan ternak',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                'pcs',
                '',
                '',
                '',
                'Taburkan 1 gram per liter air minum',
                'Hindari sinar matahari langsung saat penyimpanan',
                1,
            ],
            [
                'Daging Sapi Has Dalam',
                'Daging Segar',
                'fresh',
                125000,
                50,
                'Daging sapi has dalam segar langsung dari pemotongan',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                1.5,
                'kg',
                '',
                '',
                '',
                '',
                '',
                1,
            ],
        ];

        $output = fopen('php://output', 'w');

        // BOM for UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Headers
        fputcsv($output, $headers);

        // Sample rows
        foreach ($sampleRows as $row) {
            fputcsv($output, $row);
        }

        fclose($output);
    }
}
