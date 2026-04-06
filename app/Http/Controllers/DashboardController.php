<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

use Illuminate\Http\JsonResponse;

use App\Models\GeneralSetting;

class DashboardController extends Controller
{
    public function __construct(protected DashboardService $dashboardService)
    {
    }

    /**
     * Display the dashboard.
     */
    public function index(): View
    {
        $summary = $this->dashboardService->getSummaryStats();
        $transactions = $this->dashboardService->getTransactionStats();
        $recentSales = $this->dashboardService->getRecentSales();
        $settings = GeneralSetting::first();

        return view('dashboard', [
            'summary' => $summary,
            'transactions' => $transactions,
            'recentSales' => $recentSales,
            'settings' => $settings,
        ]);
    }

    /**
     * Get dashboard statistics.
     */
    public function stats(): JsonResponse
    {
        return response()->json([
            'summary' => $this->dashboardService->getSummaryStats(),
            'transactions' => $this->dashboardService->getTransactionStats(),
        ]);
    }
}
