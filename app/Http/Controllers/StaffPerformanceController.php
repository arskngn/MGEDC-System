<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\StaffTarget;
use App\Models\Sale;
use App\Models\SaleReturn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StaffPerformanceController extends Controller
{
    /**
     * Display the supervisor dashboard.
     */
    public function dashboard(Request $request)
    {
        $targetType = $request->get('type', 'monthly'); // daily, weekly, monthly
        
        // Define date range based on type
        [$startDate, $endDate] = $this->getDateRange($targetType);

        // Fetch staff with their targets and sales in one optimized query
        $staffPerformance = User::whereHas('roles', function($q) {
                $q->where('name', 'staff');
            })
            ->with(['targets' => function ($query) use ($targetType, $startDate, $endDate) {
                $query->where('target_type', $targetType)
                      ->where('start_date', '<=', $endDate)
                      ->where('end_date', '>=', $startDate);
            }])
            ->get()
            ->map(function ($user) use ($startDate, $endDate) {
                // Sum sales within the period
                $grossSales = Sale::where('user_id', $user->id)
                    ->whereBetween('sale_date', [$startDate, $endDate])
                    ->sum('receivable_amount');

                // Subtract returns within the period
                $returns = SaleReturn::whereHas('sale', function($q) use ($user) {
                        $q->where('user_id', $user->id);
                    })
                    ->whereBetween('return_date', [$startDate, $endDate])
                    ->sum('payable_amount');

                $actualSales = max(0, (float)$grossSales - (float)$returns);

                /** @var StaffTarget|null $target */
                $target = $user->targets->first();
                $targetAmount = $target ? (float)$target->target_amount : 0;
                
                $progress = $targetAmount > 0 ? ($actualSales / $targetAmount) * 100 : 0;
                
                return (object)[
                    'user' => $user,
                    'target_amount' => $targetAmount,
                    'actual_sales' => $actualSales,
                    'progress' => round($progress, 2),
                    'status' => $target ? $target->getStatus($progress) : 'No Target',
                    'color' => $target ? $target->getStatusColor($progress) : 'gray',
                ];
            })
            ->sortByDesc('actual_sales');

        $topPerformers = $staffPerformance->take(5);

        return view('staff.performance.dashboard', [
            'staffPerformance' => $staffPerformance,
            'topPerformers' => $topPerformers,
            'targetType' => $targetType,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    /**
     * Display individual staff performance.
     */
    public function show(Request $request, User $user)
    {
        $targetType = $request->get('type', 'monthly');
        [$startDate, $endDate] = $this->getDateRange($targetType);

        // Fetch targets for the user
        $targets = StaffTarget::where('user_id', $user->id)
            ->orderBy('start_date', 'desc')
            ->get();

        // Current performance
        $grossSales = Sale::where('user_id', $user->id)
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->sum('receivable_amount');

        $returns = SaleReturn::whereHas('sale', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->whereBetween('return_date', [$startDate, $endDate])
            ->sum('payable_amount');

        $actualSales = max(0, (float)$grossSales - (float)$returns);

        /** @var StaffTarget|null $currentTarget */
        $currentTarget = $user->targets()
            ->where('target_type', $targetType)
            ->where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate)
            ->first();

        $targetAmount = $currentTarget ? (float)$currentTarget->target_amount : 0;
        $progress = $targetAmount > 0 ? ($actualSales / $targetAmount) * 100 : 0;

        $performance = (object)[
            'user' => $user,
            'target_amount' => $targetAmount,
            'actual_sales' => (float)$actualSales,
            'progress' => round($progress, 2),
            'status' => $currentTarget ? $currentTarget->getStatus($progress) : 'No Target',
            'color' => $currentTarget ? $currentTarget->getStatusColor($progress) : 'gray',
        ];

        // Monthly sales history for chart or list
        $salesHistory = Sale::where('user_id', $user->id)
            ->selectRaw('MONTH(sale_date) as month, SUM(receivable_amount) as total')
            ->whereYear('sale_date', now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('staff.performance.show', [
            'user' => $user,
            'performance' => $performance,
            'targets' => $targets,
            'targetType' => $targetType,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'salesHistory' => $salesHistory,
        ]);
    }

    /**
     * Assign a target to a staff member.
     */
    public function assignTarget(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'target_amount' => 'required|numeric|min:0',
            'target_type' => 'required|in:daily,weekly,monthly',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        StaffTarget::updateOrCreate(
            [
                'user_id' => $validated['user_id'],
                'target_type' => $validated['target_type'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
            ],
            ['target_amount' => $validated['target_amount']]
        );

        return back()->with('success', 'Target assigned successfully.');
    }

    /**
     * Helper to get date ranges.
     */
    private function getDateRange(string $type): array
    {
        $now = Carbon::now();
        
        return match ($type) {
            'daily' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            'weekly' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'monthly' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            default => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
        };
    }
}
