<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class ExportService
{
    public function getDateRange(string $range): array
    {
        return match ($range) {
            'today' => [
                'start' => now()->startOfDay(),
                'end' => now()->endOfDay(),
            ],
            'yesterday' => [
                'start' => now()->subDay()->startOfDay(),
                'end' => now()->subDay()->endOfDay(),
            ],
            'last_7_days' => [
                'start' => now()->subDays(7)->startOfDay(),
                'end' => now()->endOfDay(),
            ],
            'last_30_days' => [
                'start' => now()->subDays(30)->startOfDay(),
                'end' => now()->endOfDay(),
            ],
            'this_month' => [
                'start' => now()->startOfMonth(),
                'end' => now()->endOfDay(),
            ],
            'last_month' => [
                'start' => now()->subMonth()->startOfMonth(),
                'end' => now()->subMonth()->endOfMonth(),
            ],
            default => [
                'start' => now()->subDays(30)->startOfDay(),
                'end' => now()->endOfDay(),
            ],
        };
    }

    public function exportSalesReport(string $format, string $range): \Symfony\Component\HttpFoundation\StreamedResponse|string
    {
        $dates = $this->getDateRange($range);
        $analytics = new AnalyticsService($dates['start'], $dates['end']);

        $data = [
            'title' => 'Sales Report',
            'period' => $dates['start']->format('d M Y') . ' - ' . $dates['end']->format('d M Y'),
            'kpi' => $analytics->getKpiCards(),
            'salesData' => $analytics->getDailySales(30),
            'topProducts' => $analytics->getTopSellingProducts(10),
            'financialSummary' => $analytics->getFinancialSummary(),
            'generated_at' => now()->format('d M Y H:i:s'),
        ];

        return $this->generateExport($data, 'sales', $format, 'sales_report');
    }

    public function exportRevenueReport(string $format, string $range): \Symfony\Component\HttpFoundation\StreamedResponse|string
    {
        $dates = $this->getDateRange($range);
        $analytics = new AnalyticsService($dates['start'], $dates['end']);

        $data = [
            'title' => 'Revenue Report',
            'period' => $dates['start']->format('d M Y') . ' - ' . $dates['end']->format('d M Y'),
            'financialSummary' => $analytics->getFinancialSummary(),
            'dailySales' => $analytics->getDailySales(30),
            'monthlySales' => $analytics->getMonthlySales(12),
            'generated_at' => now()->format('d M Y H:i:s'),
        ];

        return $this->generateExport($data, 'revenue', $format, 'revenue_report');
    }

    public function exportOrdersReport(string $format, string $range): \Symfony\Component\HttpFoundation\StreamedResponse|string
    {
        $dates = $this->getDateRange($range);
        $analytics = new AnalyticsService($dates['start'], $dates['end']);

        $data = [
            'title' => 'Orders Report',
            'period' => $dates['start']->format('d M Y') . ' - ' . $dates['end']->format('d M Y'),
            'kpi' => $analytics->getKpiCards(),
            'bookingStats' => $analytics->getBookingStats(),
            'generated_at' => now()->format('d M Y H:i:s'),
        ];

        return $this->generateExport($data, 'orders', $format, 'orders_report');
    }

    public function exportCustomersReport(string $format, string $range): \Symfony\Component\HttpFoundation\StreamedResponse|string
    {
        $dates = $this->getDateRange($range);
        $analytics = new AnalyticsService($dates['start'], $dates['end']);

        $data = [
            'title' => 'Customers Report',
            'period' => $dates['start']->format('d M Y') . ' - ' . $dates['end']->format('d M Y'),
            'newCustomers' => $analytics->getNewCustomers(100),
            'repeatCustomers' => $analytics->getRepeatCustomers(100),
            'topBuyers' => $analytics->getTopBuyers(100),
            'customerGrowth' => $analytics->getCustomerGrowth(),
            'generated_at' => now()->format('d M Y H:i:s'),
        ];

        return $this->generateExport($data, 'customers', $format, 'customers_report');
    }

    public function exportFarmersReport(string $format, string $range): \Symfony\Component\HttpFoundation\StreamedResponse|string
    {
        $dates = $this->getDateRange($range);
        $analytics = new AnalyticsService($dates['start'], $dates['end']);

        $data = [
            'title' => 'Farmers Report',
            'period' => $dates['start']->format('d M Y') . ' - ' . $dates['end']->format('d M Y'),
            'topFarmers' => $analytics->getTopFarmers(50),
            'highestRevenueFarmers' => $analytics->getHighestRevenueFarmers(50),
            'bestRatedFarmers' => $analytics->getBestRatedFarmers(50),
            'generated_at' => now()->format('d M Y H:i:s'),
        ];

        return $this->generateExport($data, 'farmers', $format, 'farmers_report');
    }

    public function exportProductsReport(string $format, string $range): \Symfony\Component\HttpFoundation\StreamedResponse|string
    {
        $dates = $this->getDateRange($range);
        $analytics = new AnalyticsService($dates['start'], $dates['end']);

        $data = [
            'title' => 'Products Report',
            'period' => $dates['start']->format('d M Y') . ' - ' . $dates['end']->format('d M Y'),
            'topSellingProducts' => $analytics->getTopSellingProducts(100),
            'topSellingLivestock' => $analytics->getTopSellingLivestock(50),
            'lowStockProducts' => $analytics->getLowStockProducts(100),
            'outOfStockProducts' => $analytics->getOutOfStockProducts(100),
            'mostViewedProducts' => $analytics->getMostViewedProducts(100),
            'generated_at' => now()->format('d M Y H:i:s'),
        ];

        return $this->generateExport($data, 'products', $format, 'products_report');
    }

    public function exportFarmerReport(string $format, string $range, int $farmerId): \Symfony\Component\HttpFoundation\StreamedResponse|string
    {
        $dates = $this->getDateRange($range);
        $analytics = new FarmerAnalyticsService($farmerId, $dates['start'], $dates['end']);

        $data = [
            'title' => 'Farm Performance Report',
            'period' => $dates['start']->format('d M Y') . ' - ' . $dates['end']->format('d M Y'),
            'kpi' => $analytics->getKpiCards(),
            'topProducts' => $analytics->getTopSellingProducts(50),
            'financialSummary' => $analytics->getFinancialSummary(),
            'recentReviews' => $analytics->getRecentReviews(20),
            'ratingDistribution' => $analytics->getRatingDistribution(),
            'generated_at' => now()->format('d M Y H:i:s'),
        ];

        return $this->generateExport($data, 'farmer', $format, 'farm_report');
    }

    protected function generateExport(array $data, string $type, string $format, string $filename): \Symfony\Component\HttpFoundation\StreamedResponse|string
    {
        return match ($format) {
            'csv' => $this->generateCsv($data, $type, $filename),
            'excel' => $this->generateExcel($data, $type, $filename),
            'pdf' => $this->generatePdf($data, $type, $filename),
            default => throw new \InvalidArgumentException("Unsupported format: {$format}"),
        };
    }

    protected function generateCsv(array $data, string $type, string $filename): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $response = new \Symfony\Component\HttpFoundation\StreamedResponse(function () use ($data, $type) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [$data['title']]);
            fputcsv($handle, ['Period: ' . $data['period']]);
            fputcsv($handle, ['Generated: ' . $data['generated_at']]);
            fputcsv($handle, []);

            switch ($type) {
                case 'sales':
                    $this->csvSalesData($handle, $data);
                    break;
                case 'revenue':
                    $this->csvRevenueData($handle, $data);
                    break;
                case 'customers':
                    $this->csvCustomersData($handle, $data);
                    break;
                case 'farmers':
                    $this->csvFarmersData($handle, $data);
                    break;
                case 'products':
                    $this->csvProductsData($handle, $data);
                    break;
                case 'farmer':
                    $this->csvFarmerData($handle, $data);
                    break;
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', "attachment; filename=\"{$filename}_" . now()->format('Y-m-d') . ".csv\"");

        return $response;
    }

    protected function csvSalesData($handle, array $data): void
    {
        fputcsv($handle, ['=== KPI Summary ===']);
        fputcsv($handle, ['Metric', 'Value']);
        fputcsv($handle, ['Total Revenue', 'Rp ' . number_format($data['kpi']['total_revenue'], 0, ',', '.')]);
        fputcsv($handle, ['Total Orders', $data['kpi']['total_orders']]);
        fputcsv($handle, ['Completed Orders', $data['kpi']['completed_orders']]);
        fputcsv($handle, ['Pending Orders', $data['kpi']['pending_orders']]);
        fputcsv($handle, ['Active Customers', $data['kpi']['active_customers']]);
        fputcsv($handle, []);

        fputcsv($handle, ['=== Daily Sales ===']);
        fputcsv($handle, ['Date', 'Orders', 'Revenue']);
        foreach ($data['salesData'] as $sale) {
            fputcsv($handle, [$sale['date'], $sale['orders'], $sale['revenue']]);
        }
        fputcsv($handle, []);

        fputcsv($handle, ['=== Top Products ===']);
        fputcsv($handle, ['Rank', 'Product', 'Category', 'Quantity Sold', 'Revenue']);
        foreach ($data['topProducts'] as $index => $product) {
            fputcsv($handle, [$index + 1, $product['name'], $product['category'], $product['quantity'], $product['revenue']]);
        }
    }

    protected function csvRevenueData($handle, array $data): void
    {
        fputcsv($handle, ['=== Financial Summary ===']);
        fputcsv($handle, ['Metric', 'Value']);
        fputcsv($handle, ['Gross Revenue', 'Rp ' . number_format($data['financialSummary']['gross_revenue'], 0, ',', '.')]);
        fputcsv($handle, ['Discount Total', 'Rp ' . number_format($data['financialSummary']['discount_total'], 0, ',', '.')]);
        fputcsv($handle, ['Promotion Cost', 'Rp ' . number_format($data['financialSummary']['promotion_cost'], 0, ',', '.')]);
        fputcsv($handle, ['Shipping Revenue', 'Rp ' . number_format($data['financialSummary']['shipping_revenue'], 0, ',', '.')]);
        fputcsv($handle, ['Net Revenue', 'Rp ' . number_format($data['financialSummary']['net_revenue'], 0, ',', '.')]);
        fputcsv($handle, []);

        fputcsv($handle, ['=== Monthly Sales ===']);
        fputcsv($handle, ['Month', 'Revenue']);
        foreach ($data['monthlySales'] as $sale) {
            fputcsv($handle, [$sale['label'], $sale['revenue']]);
        }
    }

    protected function csvCustomersData($handle, array $data): void
    {
        fputcsv($handle, ['=== Customer Growth ===']);
        fputcsv($handle, ['Metric', 'Value']);
        fputcsv($handle, ['New Customers', $data['customerGrowth']['current']]);
        fputcsv($handle, ['Growth Rate', $data['customerGrowth']['growth'] . '%']);
        fputcsv($handle, []);

        fputcsv($handle, ['=== Top Buyers ===']);
        fputcsv($handle, ['Rank', 'Name', 'Email', 'Total Spent', 'Order Count']);
        foreach ($data['topBuyers'] as $index => $buyer) {
            fputcsv($handle, [$index + 1, $buyer['name'], $buyer['email'], $buyer['total_spent'], $buyer['order_count']]);
        }
    }

    protected function csvFarmersData($handle, array $data): void
    {
        fputcsv($handle, ['=== Top Farmers by Revenue ===']);
        fputcsv($handle, ['Rank', 'Name', 'Shop', 'Revenue', 'Rating', 'Trust Score']);
        foreach ($data['highestRevenueFarmers'] as $index => $farmer) {
            fputcsv($handle, [$index + 1, $farmer['name'], $farmer['shop_name'], $farmer['revenue'], $farmer['rating'], $farmer['trust_score']]);
        }
    }

    protected function csvProductsData($handle, array $data): void
    {
        fputcsv($handle, ['=== Top Selling Products ===']);
        fputcsv($handle, ['Rank', 'Product', 'Category', 'Quantity Sold', 'Revenue']);
        foreach ($data['topSellingProducts'] as $index => $product) {
            fputcsv($handle, [$index + 1, $product['name'], $product['category'], $product['quantity'], $product['revenue']]);
        }
        fputcsv($handle, []);

        fputcsv($handle, ['=== Out of Stock Products ===']);
        fputcsv($handle, ['Product', 'Category']);
        foreach ($data['outOfStockProducts'] as $product) {
            fputcsv($handle, [$product['name'], $product['category']]);
        }
    }

    protected function csvFarmerData($handle, array $data): void
    {
        fputcsv($handle, ['=== Farm Performance ===']);
        fputcsv($handle, ['Metric', 'Value']);
        fputcsv($handle, ['Total Revenue', 'Rp ' . number_format($data['kpi']['total_revenue'], 0, ',', '.')]);
        fputcsv($handle, ['Total Orders', $data['kpi']['total_orders']]);
        fputcsv($handle, ['Average Rating', $data['kpi']['average_rating']]);
        fputcsv($handle, ['Total Reviews', $data['kpi']['total_reviews']]);
        fputcsv($handle, []);

        fputcsv($handle, ['=== Top Products ===']);
        fputcsv($handle, ['Rank', 'Product', 'Quantity Sold', 'Revenue']);
        foreach ($data['topProducts'] as $index => $product) {
            fputcsv($handle, [$index + 1, $product['name'], $product['quantity'], $product['revenue']]);
        }
    }

    protected function generateExcel(array $data, string $type, string $filename): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $response = new \Symfony\Component\HttpFoundation\StreamedResponse(function () use ($data, $type) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [$data['title']]);
            fputcsv($handle, ['Period: ' . $data['period']]);
            fputcsv($handle, ['Generated: ' . $data['generated_at']]);

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', "attachment; filename=\"{$filename}_" . now()->format('Y-m-d') . ".xls\"");

        return $response;
    }

    protected function generatePdf(array $data, string $type, string $filename): string
    {
        $html = view('exports.analytics.' . $type, $data)->render();

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'landscape',
        ]);

        $mpdf->WriteHTML($html);
        $mpdf->Output($filename . '_' . now()->format('Y-m-d') . '.pdf', 'D');

        return '';
    }
}
