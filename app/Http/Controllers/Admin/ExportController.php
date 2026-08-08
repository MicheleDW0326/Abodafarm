<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ExportService;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function __construct(
        protected ExportService $exportService
    ) {}

    public function analytics(Request $request)
    {
        $type = $request->get('type', 'sales');
        $format = $request->get('format', 'csv');
        $range = $request->get('range', 'last_30_days');

        return match ($type) {
            'sales' => $this->exportService->exportSalesReport($format, $range),
            'revenue' => $this->exportService->exportRevenueReport($format, $range),
            'orders' => $this->exportService->exportOrdersReport($format, $range),
            'customers' => $this->exportService->exportCustomersReport($format, $range),
            'farmers' => $this->exportService->exportFarmersReport($format, $range),
            'products' => $this->exportService->exportProductsReport($format, $range),
            default => $this->exportService->exportSalesReport($format, $range),
        };
    }
}
