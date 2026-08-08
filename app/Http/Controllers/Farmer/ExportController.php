<?php

namespace App\Http\Controllers\Farmer;

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
        $format = $request->get('format', 'csv');
        $range = $request->get('range', 'last_30_days');
        $farmerId = auth()->id();

        return $this->exportService->exportFarmerReport($format, $range, $farmerId);
    }
}
