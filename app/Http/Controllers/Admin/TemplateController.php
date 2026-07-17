<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TemplateController extends Controller
{
    /**
     * List of available templates.
     */
    protected array $templates = [
        'livestock' => [
            'file' => 'template_produk_ternak.xlsx',
            'name' => 'Template Produk Ternak',
            'description' => 'Untuk import produk hewan ternak (sapi, kambing, dll)',
            'type' => 'live',
        ],
        'fresh' => [
            'file' => 'template_produk_daging.xlsx',
            'name' => 'Template Produk Daging',
            'description' => 'Untuk import produk daging segar',
            'type' => 'fresh',
        ],
        'supply' => [
            'file' => 'template_produk_pakan.xlsx',
            'name' => 'Template Pakan & Suplimen',
            'description' => 'Untuk import produk pakan dan perlengkapan ternak',
            'type' => 'supply',
        ],
    ];

    /**
     * Download a template file.
     */
    public function download(string $type): StreamedResponse|\Illuminate\Http\RedirectResponse
    {
        if (!isset($this->templates[$type])) {
            abort(404, 'Template tidak ditemukan');
        }

        $template = $this->templates[$type];
        $filePath = 'templates/' . $template['file'];

        // Store template type in session for auto-detection during upload
        session(['import_template_type' => $template['type']]);

        // Check if file exists
        if (!Storage::disk('public')->exists($filePath)) {
            return $this->downloadCsvFallback($type, $template);
        }

        $downloadName = $template['file'];

        return Storage::disk('public')->download(
            $filePath,
            $downloadName,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $downloadName . '"',
            ]
        );
    }

    /**
     * Download CSV fallback when Excel file doesn't exist yet.
     */
    protected function downloadCsvFallback(string $type, array $template): StreamedResponse
    {
        $headers = $this->getHeadersForType($type);
        $sampleRows = $this->getSampleRowsForType($type);

        $fileName = str_replace('.xlsx', '.csv', $template['file']);

        return response()->streamDownload(function () use ($headers, $sampleRows) {
            $output = fopen('php://output', 'w');

            // BOM for UTF-8
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Headers
            fputcsv($output, $headers);

            // Sample rows
            foreach ($sampleRows as $row) {
                fputcsv($output, $row);
            }

            fclose($output);
        }, $fileName, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    /**
     * Get headers for a specific template type.
     * No "Tipe Produk" column - type is auto-detected from session.
     */
    protected function getHeadersForType(string $type): array
    {
        return match ($type) {
            'livestock' => [
                'Nama Produk', 'Kategori', 'Harga', 'Stok', 'Deskripsi',
                'Ras', 'Berat (kg)', 'Umur', 'Jenis Kelamin', 'Asal',
                'Kondisi', 'Lokasi Kandang', 'Berat Per Unit (kg)', 'Unit',
                'Harga Diskon', 'Diskon (%)', 'Spesifikasi', 'Cara Penggunaan',
                'Info Pengiriman', 'Unggulan',
            ],
            'fresh' => [
                'Nama Produk', 'Kategori', 'Harga', 'Stok', 'Deskripsi',
                'Berat Per Unit (kg)', 'Unit', 'Harga Diskon', 'Diskon (%)',
                'Spesifikasi', 'Info Pengiriman', 'Unggulan',
            ],
            'supply' => [
                'Nama Produk', 'Kategori', 'Harga', 'Stok', 'Deskripsi',
                'Unit', 'Harga Diskon', 'Diskon (%)', 'Spesifikasi',
                'Cara Penggunaan', 'Info Pengiriman', 'Unggulan',
            ],
            default => [],
        };
    }

    /**
     * Get sample rows for a specific template type.
     * No "Tipe Produk" column - type is auto-detected from session.
     */
    protected function getSampleRowsForType(string $type): array
    {
        return match ($type) {
            'livestock' => [
                [
                    'Sapi Limousin Betina Premium', 'Sapi', 15000000, 5,
                    'Sapi limousin betina usia 2 tahun kondisi sehat siap kawin',
                    'Limousin', 350.5, '2 Tahun', 'female', 'Jawa Timur',
                    'Sehat', 'Kandang A', '', '', '', '', '', '', '', 0,
                ],
                [
                    'Kambing Etawa PE', 'Kambing', 8500000, 3,
                    'Kambing etawa perah impor berkualitas tinggi',
                    'Etawa PE', 45, '1.5 Tahun', 'male', 'Jawa Barat',
                    'Sehat', 'Kandang B', '', '', '', '', '', '', '', 1,
                ],
            ],
            'fresh' => [
                [
                    'Daging Sapi Has Dalam Segar', 'Daging Segar', 125000, 50,
                    'Daging sapi has dalam segar langsung dari pemotongan',
                    1.5, 'kg', '', '', '', '', 1,
                ],
                [
                    'Daging Ayam Broiler Segar', 'Daging Segar', 35000, 100,
                    'Daging ayam broiler segar dari peternakan lokal',
                    1, 'kg', 32000, 8, '', '', 0,
                ],
            ],
            'supply' => [
                [
                    'Pakan Ayam Starter 25kg', 'Pakan Ternak', 450000, 100,
                    'Pakan ayam starter untuk anak ayam umur 1-3 minggu',
                    'kg', 450000, 10,
                    'Komposisi: Jagung 50%, Dedak 20%, Bungkil 30%',
                    'Campurkan 100g per liter air minum',
                    'Gratis ongkir untuk pembelian min 10 sack', 0,
                ],
                [
                    'Vitamin B-Complex', 'Vitamin & Suplemen', 75000, 500,
                    'Vitamin B kompleks untuk meningkatkan nafsu makan ternak',
                    'pcs', '', '',
                    '',
                    'Taburkan 1 gram per liter air minum',
                    'Hindari sinar matahari langsung saat penyimpanan', 1,
                ],
            ],
            default => [],
        };
    }
}
