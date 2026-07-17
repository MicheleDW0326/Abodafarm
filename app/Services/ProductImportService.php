<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;
use ZipArchive;

class ProductImportService
{
    public array $rows = [];
    public array $validRows = [];
    public array $invalidRows = [];
    public array $errors = [];
    public int $totalRows = 0;
    public int $validCount = 0;
    public int $invalidCount = 0;
    public int $importedCount = 0;
    public int $skippedCount = 0;

    protected array $categoryMap = [];
    protected array $validTypes = ['live', 'fresh', 'supply'];
    protected array $validSexes = ['male', 'female', 'mixed'];
    protected array $processedNames = [];
    protected string $templateType = 'live';

    public function __construct()
    {
        $this->loadCategories();
        $this->loadTemplateType();
    }

    protected function loadCategories(): void
    {
        $categories = Category::where('is_active', true)->get();
        foreach ($categories as $category) {
            $this->categoryMap[strtolower($category->name)] = $category->id;
        }
    }

    protected function loadTemplateType(): void
    {
        $this->templateType = session('import_template_type', 'live');

        if (!in_array($this->templateType, $this->validTypes)) {
            $this->templateType = 'live';
        }
    }

    public function processFile($file): void
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if ($extension === 'csv') {
            $this->processCsv($file);
        } else {
            $this->processExcel($file);
        }
    }

    /**
     * Detect template type from file header.
     * Called when session is not available or invalid.
     */
    protected function detectTypeFromFile($file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if ($extension === 'csv') {
            return $this->detectTypeFromCsv($file);
        } else {
            return $this->detectTypeFromExcel($file);
        }
    }

    protected function detectTypeFromCsv($file): string
    {
        $path = $file->getRealPath();
        $handle = fopen($path, 'r');

        if ($handle === false) {
            return 'live';
        }

        $header = fgetcsv($handle);
        fclose($handle);

        if (!$header || !is_array($header)) {
            return 'live';
        }

        // Remove BOM if present
        if (isset($header[0]) && substr($header[0], 0, 3) === "\xEF\xBB\xBF") {
            $header[0] = substr($header[0], 3);
        }

        // Convert to lowercase for comparison
        $headerLower = array_map('strtolower', $header);
        $headerStr = implode(',', $headerLower);

        // Detect based on column count and headers
        // Livestock: has "ras", "berat (kg)", "umur", "jenis kelamin", "asal"
        if (strpos($headerStr, 'ras') !== false && strpos($headerStr, 'jenis kelamin') !== false) {
            return 'live';
        }

        // Supply: has "unit", "spesifikasi", "cara penggunaan" but no livestock fields
        if (strpos($headerStr, 'cara penggunaan') !== false && strpos($headerStr, 'jenis kelamin') === false) {
            return 'supply';
        }

        // Fresh: has "berat per unit" and "spesifikasi" but no livestock fields
        if (strpos($headerStr, 'berat per unit') !== false && strpos($headerStr, 'jenis kelamin') === false) {
            return 'fresh';
        }

        // Default fallback
        return 'live';
    }

    protected function detectTypeFromExcel($file): string
    {
        $path = $file->getRealPath();

        if (!class_exists('ZipArchive')) {
            return 'live';
        }

        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            return 'live';
        }

        // Read shared strings for header
        $sharedStrings = [];
        if ($zip->locateName('xl/sharedStrings.xml') !== false) {
            $stringsXml = $zip->getFromName('xl/sharedStrings.xml');
            $stringsDoc = new \SimpleXMLElement($stringsXml);
            $stringsDoc->registerXPathNamespace('s', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

            foreach ($stringsDoc->xpath('//s:si') as $string) {
                $text = '';
                foreach ($string->t as $t) {
                    $text .= (string) $t;
                }
                $sharedStrings[] = $text;
            }
        }

        // Read first row
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if (!$sheetXml) {
            return 'live';
        }

        $sheetDoc = new \SimpleXMLElement($sheetXml);
        $sheetDoc->registerXPathNamespace('s', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

        $firstRow = $sheetDoc->xpath('//s:row[1]');
        if (!$firstRow || !isset($firstRow[0])) {
            return 'live';
        }

        $header = [];
        foreach ($firstRow[0]->c as $cell) {
            $type = (string) ($cell['t'] ?? '');
            $value = (string) ($cell->v ?? '');

            if ($type === 's' && isset($sharedStrings[(int) $value])) {
                $value = $sharedStrings[(int) $value];
            }

            $header[] = strtolower($value);
        }

        $headerStr = implode(',', $header);

        // Detect based on headers
        if (strpos($headerStr, 'ras') !== false && strpos($headerStr, 'jenis kelamin') !== false) {
            return 'live';
        }

        if (strpos($headerStr, 'cara penggunaan') !== false && strpos($headerStr, 'jenis kelamin') === false) {
            return 'supply';
        }

        if (strpos($headerStr, 'berat per unit') !== false && strpos($headerStr, 'jenis kelamin') === false) {
            return 'fresh';
        }

        return 'live';
    }

    protected function processCsv($file): void
    {
        $path = $file->getRealPath();
        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw new \Exception('Tidak dapat membuka file CSV');
        }

        $rows = [];
        $headerSkipped = false;

        // Check if session type is valid, if not detect from file
        $sessionType = session('import_template_type', null);
        if (!$sessionType || !in_array($sessionType, $this->validTypes)) {
            // Read first line to detect type
            $firstLine = fgets($handle);
            rewind($handle);

            if ($firstLine) {
                $firstData = fgetcsv($handle);
                $firstData[0] = str_replace("\xEF\xBB\xBF", '', $firstData[0]);
                $headerLower = array_map(function($v) { return strtolower(trim($v ?? '')); }, $firstData);
                $headerStr = implode(',', $headerLower);

                if (strpos($headerStr, 'ras') !== false && strpos($headerStr, 'jenis kelamin') !== false) {
                    $this->templateType = 'live';
                } elseif (strpos($headerStr, 'cara penggunaan') !== false && strpos($headerStr, 'jenis kelamin') === false) {
                    $this->templateType = 'supply';
                } elseif (strpos($headerStr, 'berat per unit') !== false && strpos($headerStr, 'jenis kelamin') === false) {
                    $this->templateType = 'fresh';
                } else {
                    $this->templateType = 'live';
                }
            }
        }

        while (($data = fgetcsv($handle, 0, ',')) !== false) {
            // Skip BOM if present
            if (count($rows) === 0 && isset($data[0]) && substr($data[0], 0, 3) === "\xEF\xBB\xBF") {
                $data[0] = substr($data[0], 3);
            }

            // Skip header row
            if (!$headerSkipped && $this->looksLikeHeader($data)) {
                $headerSkipped = true;
                continue;
            }

            // Skip empty rows
            if ($this->isEmptyRow($data)) {
                continue;
            }

            $rows[] = $data;
        }

        fclose($handle);

        $this->rows = $rows;
        $this->totalRows = count($rows);

        $rowIndex = 1;
        foreach ($rows as $row) {
            $result = $this->validateRow($row, $rowIndex);

            if ($result['valid']) {
                $this->validRows[] = [
                    'row_number' => $rowIndex,
                    'data' => $result['data'],
                ];
                $this->validCount++;
            } else {
                $this->invalidRows[] = [
                    'row_number' => $rowIndex,
                    'errors' => $result['errors'],
                    'data' => $row,
                ];
                $this->invalidCount++;
            }

            $rowIndex++;
        }
    }

    protected function processExcel($file): void
    {
        $path = $file->getRealPath();

        if (!class_exists('ZipArchive')) {
            throw new \Exception('ZipArchive diperlukan untuk membaca file Excel');
        }

        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new \Exception('Tidak dapat membuka file Excel');
        }

        // Read shared strings
        $sharedStrings = [];
        if ($zip->locateName('xl/sharedStrings.xml') !== false) {
            $stringsXml = $zip->getFromName('xl/sharedStrings.xml');
            $stringsDoc = new \SimpleXMLElement($stringsXml);
            $stringsDoc->registerXPathNamespace('s', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

            foreach ($stringsDoc->xpath('//s:si') as $string) {
                $text = '';
                foreach ($string->t as $t) {
                    $text .= (string) $t;
                }
                $sharedStrings[] = $text;
            }
        }

        // Read first sheet
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if (!$sheetXml) {
            throw new \Exception('Tidak dapat membaca sheet dari file Excel');
        }

        $sheetDoc = new \SimpleXMLElement($sheetXml);
        $sheetDoc->registerXPathNamespace('s', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

        // Check if session type is valid, if not detect from file
        $sessionType = session('import_template_type', null);
        if (!$sessionType || !in_array($sessionType, $this->validTypes)) {
            // Detect type from first row
            $firstRow = $sheetDoc->xpath('//s:row[1]');
            if ($firstRow && isset($firstRow[0])) {
                $headerData = [];
                foreach ($firstRow[0]->c as $cell) {
                    $type = (string) ($cell['t'] ?? '');
                    $value = (string) ($cell->v ?? '');
                    if ($type === 's' && isset($sharedStrings[(int) $value])) {
                        $value = $sharedStrings[(int) $value];
                    }
                    $headerData[] = strtolower($value);
                }
                $headerStr = implode(',', $headerData);

                if (strpos($headerStr, 'ras') !== false && strpos($headerStr, 'jenis kelamin') !== false) {
                    $this->templateType = 'live';
                } elseif (strpos($headerStr, 'cara penggunaan') !== false && strpos($headerStr, 'jenis kelamin') === false) {
                    $this->templateType = 'supply';
                } elseif (strpos($headerStr, 'berat per unit') !== false && strpos($headerStr, 'jenis kelamin') === false) {
                    $this->templateType = 'fresh';
                } else {
                    $this->templateType = 'live';
                }
            }
        }

        $rows = [];
        $headerSkipped = false;

        foreach ($sheetDoc->xpath('//s:row') as $row) {
            $rowIndex = (int) $row['r'];
            if ($rowIndex < 1) continue;

            $rowData = [];
            $colIndex = 0;

            foreach ($row->c as $cell) {
                $cellRef = (string) $cell['r'];
                $cellCol = preg_replace('/[0-9]/', '', $cellRef);
                $cellColIndex = $this->columnLetterToIndex($cellCol);

                while ($colIndex < $cellColIndex) {
                    $rowData[] = '';
                    $colIndex++;
                }

                $type = (string) ($cell['t'] ?? '');
                $value = (string) ($cell->v ?? '');

                if ($type === 's' && isset($sharedStrings[(int) $value])) {
                    $value = $sharedStrings[(int) $value];
                } elseif ($type === 'b') {
                    $value = $value === '1' ? 'true' : 'false';
                }

                $rowData[] = $value;
                $colIndex++;
            }

            if (!$headerSkipped && $this->looksLikeHeader($rowData)) {
                $headerSkipped = true;
                continue;
            }

            if ($this->isEmptyRow($rowData)) {
                continue;
            }

            $rows[] = $rowData;
        }

        $this->rows = $rows;
        $this->totalRows = count($rows);

        $rowIndex = 1;
        foreach ($rows as $row) {
            $result = $this->validateRow($row, $rowIndex);

            if ($result['valid']) {
                $this->validRows[] = [
                    'row_number' => $rowIndex,
                    'data' => $result['data'],
                ];
                $this->validCount++;
            } else {
                $this->invalidRows[] = [
                    'row_number' => $rowIndex,
                    'errors' => $result['errors'],
                    'data' => $row,
                ];
                $this->invalidCount++;
            }

            $rowIndex++;
        }
    }

    protected function columnLetterToIndex(string $letter): int
    {
        $index = 0;
        $length = strlen($letter);

        for ($i = 0; $i < $length; $i++) {
            $index = $index * 26 + (ord(strtoupper($letter[$i])) - ord('A') + 1);
        }

        return $index - 1;
    }

    protected function looksLikeHeader(array $row): bool
    {
        $firstCell = strtolower(trim($row[0] ?? ''));
        return in_array($firstCell, ['nama produk', 'name', 'product name', 'product']);
    }

    protected function isEmptyRow(array $row): bool
    {
        foreach ($row as $cell) {
            if (!empty(trim((string) $cell))) {
                return false;
            }
        }
        return true;
    }

    protected function validateRow(array $row, int $rowNumber): array
    {
        $errors = [];
        $data = [];

        $type = $this->templateType;

        // Helper function to clean and convert numeric values
        $cleanNumeric = function($value) {
            if ($value === null || $value === '') {
                return null;
            }
            // Trim and remove non-printable characters
            $cleaned = trim((string) $value);
            // Remove thousand separators (Indonesian format: 1.000.000)
            $cleaned = str_replace(['.', ' '], '', $cleaned);
            // Handle comma as decimal separator (European format: 1,5)
            $cleaned = str_replace(',', '.', $cleaned);
            return $cleaned;
        };

        // Helper function to check if value is numeric
        $isNumeric = function($value) {
            if ($value === null || $value === '') {
                return false;
            }
            return is_numeric($value);
        };

        // Extract columns based on type (columns are different for each template)
        $name = trim((string) ($row[0] ?? ''));
        $categoryName = trim((string) ($row[1] ?? ''));
        $priceRaw = $cleanNumeric($row[2] ?? '');
        $stockRaw = $cleanNumeric($row[3] ?? '');

        if ($type === 'live') {
            // Livestock template columns
            $description = trim((string) ($row[4] ?? ''));
            $breed = trim((string) ($row[5] ?? ''));
            $weightRaw = $cleanNumeric($row[6] ?? '');
            $age = trim((string) ($row[7] ?? ''));
            $sex = strtolower(trim((string) ($row[8] ?? '')));
            $origin = trim((string) ($row[9] ?? ''));
            $condition = trim((string) ($row[10] ?? ''));
            $barnLocation = trim((string) ($row[11] ?? ''));
            $weightPerUnitRaw = $cleanNumeric($row[12] ?? '');
            $unit = trim((string) ($row[13] ?? ''));
            $discountPriceRaw = $cleanNumeric($row[14] ?? '');
            $discountPercentRaw = $cleanNumeric($row[15] ?? '');
            $specifications = trim((string) ($row[16] ?? ''));
            $howToUse = trim((string) ($row[17] ?? ''));
            $shippingInfo = trim((string) ($row[18] ?? ''));
            $isFeatured = trim((string) ($row[19] ?? '0'));
        } elseif ($type === 'fresh') {
            // Fresh meat template columns
            $description = trim((string) ($row[4] ?? ''));
            $weightPerUnitRaw = $cleanNumeric($row[5] ?? '');
            $unit = trim((string) ($row[6] ?? ''));
            $discountPriceRaw = $cleanNumeric($row[7] ?? '');
            $discountPercentRaw = $cleanNumeric($row[8] ?? '');
            $specifications = trim((string) ($row[9] ?? ''));
            $shippingInfo = trim((string) ($row[10] ?? ''));
            $isFeatured = trim((string) ($row[11] ?? '0'));
            // Fresh meat doesn't have these fields
            $breed = null;
            $weightRaw = null;
            $age = null;
            $sex = null;
            $origin = null;
            $condition = null;
            $barnLocation = null;
            $howToUse = null;
        } else {
            // Supply template columns
            $description = trim((string) ($row[4] ?? ''));
            $unit = trim((string) ($row[5] ?? ''));
            $discountPriceRaw = $cleanNumeric($row[6] ?? '');
            $discountPercentRaw = $cleanNumeric($row[7] ?? '');
            $specifications = trim((string) ($row[8] ?? ''));
            $howToUse = trim((string) ($row[9] ?? ''));
            $shippingInfo = trim((string) ($row[10] ?? ''));
            $isFeatured = trim((string) ($row[11] ?? '0'));
            // Supply doesn't have these fields
            $weightPerUnitRaw = null;
            $breed = null;
            $weightRaw = null;
            $age = null;
            $sex = null;
            $origin = null;
            $condition = null;
            $barnLocation = null;
        }

        // Required fields validation
        if (empty($name)) {
            $errors[] = 'Nama Produk wajib diisi';
        }

        if (empty($categoryName)) {
            $errors[] = 'Kategori wajib diisi';
        } else {
            $categoryId = $this->categoryMap[strtolower($categoryName)] ?? null;
            if (!$categoryId) {
                $errors[] = 'Kategori "' . $categoryName . '" tidak ditemukan';
            } else {
                $data['category_id'] = $categoryId;
            }
        }

        if (empty($priceRaw)) {
            $errors[] = 'Harga wajib diisi';
        } elseif (!$isNumeric($priceRaw) || (float) $priceRaw < 0) {
            $errors[] = 'Harga harus berupa angka positif';
        }

        if (empty($stockRaw)) {
            $errors[] = 'Stok wajib diisi';
        } elseif (!$isNumeric($stockRaw) || (int) $stockRaw < 0) {
            $errors[] = 'Stok harus berupa angka positif';
        }

        // Optional field validations (only for livestock type)
        if ($type === 'live') {
            if (!empty($sex) && !in_array($sex, $this->validSexes)) {
                $errors[] = 'Jenis Kelamin harus salah satu dari: male, female, mixed';
            }

            if (!empty($weightRaw) && !$isNumeric($weightRaw)) {
                $errors[] = 'Berat harus berupa angka';
            }
        }

        if (!empty($weightPerUnitRaw) && !$isNumeric($weightPerUnitRaw)) {
            $errors[] = 'Berat Per Unit harus berupa angka';
        }

        if (!empty($discountPriceRaw) && !$isNumeric($discountPriceRaw)) {
            $errors[] = 'Harga Diskon harus berupa angka';
        }

        if (!empty($discountPercentRaw) && (!$isNumeric($discountPercentRaw) || (float) $discountPercentRaw < 0 || (float) $discountPercentRaw > 100)) {
            $errors[] = 'Diskon harus berupa angka antara 0-100';
        }

        // Duplicate detection
        if (!empty($name)) {
            $slug = Str::slug($name);
            if (isset($this->processedNames[$slug])) {
                $errors[] = 'Produk dengan nama "' . $name . '" sudah ada di baris ' . $this->processedNames[$slug];
            } else {
                $this->processedNames[$slug] = $rowNumber;
            }

            $existingProduct = Product::where('slug', $slug)->first();
            if ($existingProduct) {
                $errors[] = 'Produk dengan nama "' . $name . '" sudah ada di database';
            }
        }

        // Build validated data
        $data['name'] = $name;
        $data['category_name'] = $categoryName;
        $data['type'] = $type;
        $data['price'] = !empty($priceRaw) ? (float) $priceRaw : 0;
        $data['stock'] = !empty($stockRaw) ? (int) $stockRaw : 0;
        $data['description'] = $description;
        $data['breed'] = $breed;
        $data['weight'] = !empty($weightRaw) ? (float) $weightRaw : null;
        $data['age'] = $age;
        $data['sex'] = $sex;
        $data['origin'] = $origin;
        $data['condition'] = $condition;
        $data['barn_location'] = $barnLocation;
        $data['weight_per_unit'] = !empty($weightPerUnitRaw) ? (float) $weightPerUnitRaw : null;
        $data['unit'] = $unit;
        $data['discount_price'] = !empty($discountPriceRaw) ? (float) $discountPriceRaw : null;
        $data['discount_percent'] = !empty($discountPercentRaw) ? (float) $discountPercentRaw : null;
        $data['specifications'] = $specifications;
        $data['how_to_use'] = $howToUse;
        $data['shipping_info'] = $shippingInfo;
        $data['is_featured'] = in_array((string) $isFeatured, ['1', 'true', 'yes']) ? 1 : 0;

        return [
            'valid' => empty($errors),
            'data' => $data,
            'errors' => $errors,
        ];
    }

    public function import(): array
    {
        $this->importedCount = 0;
        $this->skippedCount = 0;

        foreach ($this->validRows as $row) {
            $data = $row['data'];

            try {
                Product::create([
                    'name' => $data['name'],
                    'slug' => Str::slug($data['name']),
                    'category_id' => $data['category_id'],
                    'type' => $data['type'],
                    'status' => 'available',
                    'price' => $data['price'],
                    'stock' => $data['stock'],
                    'description' => $data['description'] ?: null,
                    'breed' => $data['breed'] ?: null,
                    'weight' => $data['weight'],
                    'age' => $data['age'] ?: null,
                    'sex' => $data['sex'],
                    'origin' => $data['origin'] ?: null,
                    'condition' => $data['condition'] ?: null,
                    'barn_location' => $data['barn_location'] ?: null,
                    'weight_per_unit' => $data['weight_per_unit'],
                    'unit' => $data['unit'] ?: null,
                    'discount_price' => $data['discount_price'],
                    'discount_percent' => $data['discount_percent'],
                    'specifications' => $data['specifications'] ?: null,
                    'how_to_use' => $data['how_to_use'] ?: null,
                    'shipping_info' => $data['shipping_info'] ?: null,
                    'is_featured' => $data['is_featured'],
                    'owner_id' => auth()->id(),
                ]);

                $this->importedCount++;
            } catch (\Exception $e) {
                $this->skippedCount++;
                $this->errors[] = "Baris {$row['row_number']}: Gagal menyimpan - " . $e->getMessage();
            }
        }

        return [
            'imported' => $this->importedCount,
            'skipped' => $this->skippedCount,
            'errors' => $this->errors,
        ];
    }

    public function getSummary(): array
    {
        return [
            'total_rows' => $this->totalRows,
            'valid_count' => $this->validCount,
            'invalid_count' => $this->invalidCount,
            'imported_count' => $this->importedCount,
            'skipped_count' => $this->skippedCount,
        ];
    }
}
