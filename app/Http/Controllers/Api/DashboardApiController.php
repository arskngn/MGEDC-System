<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;

class DashboardApiController extends Controller
{
    public function __construct(protected DashboardService $dashboardService)
    {
    }

    public function stats(): JsonResponse
    {
        return response()->json([
            'summary' => $this->dashboardService->getSummaryStats(),
            'transactions' => $this->dashboardService->getTransactionStats(),
        ]);
    }
}
