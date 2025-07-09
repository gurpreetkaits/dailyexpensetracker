<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Repositories\StatsRepository;

class StatsController extends Controller
{
    public function __construct(private StatsRepository $statsRepository)
    {
    }
    public function index()
    {
        if (auth()->user()?->email !== 'gurpreetkait.codes@gmail.com') {
            abort(403, 'unauthorized');
        }
        $stats = [
            'totalUsers' => User::count(),
            'totalTransactions' => Transaction::count(),
            'totalAmount' => Transaction::sum('amount')
        ];

        $users = User::withCount('transactions')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([$stats, $users], 200);
    }

    public function showStats(Request $request)
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'required_if:type,custom', 'date'],
            'end_date' => ['nullable', 'required_if:type,custom', 'date', 'after_or_equal:start_date']
        ]);

        $query = Transaction::query()
            ->where('user_id', auth()->id());
        $query->whereBetween('transaction_date', [$validated['start_date'], $validated['end_date']]);


        // Get category wise expenses
        $categoryStats = $query->clone()
            ->where('type', 'expense')
            ->with('category')
            ->select('category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->get();

        // Calculate total expenses
        $totalExpenses = $categoryStats->sum('total');

        // Get stats data
        $dateRange = $this->getDateRange('custom', $validated['start_date'], $validated['end_date']);
        $stats = [
            'overview' => [
                'total_spent' => $totalExpenses,
                'avg_per_day' => $this->calculateDailyAverage($query->clone(), $dateRange),
                'most_expensive_day' => $this->getMostExpensiveDay($query->clone()),
                'previous_comparison' => $this->getPreviousPeriodComparison('custom', $dateRange['start'])
            ],
            'financial_health' => [
                'savings_rate' => $this->calculateSavingsRate($query->clone()),
                'status' => $this->getFinancialHealthStatus($query->clone())
            ],
            'categories' => $categoryStats->map(function ($category) use ($totalExpenses) {
                return [
                    'id' => $category->category_id,
                    'name' => $category->category?->name ?? 'Uncategorized',
                    'amount' => $category->total,
                    'percentage' => $totalExpenses > 0 ? round(($category->total / $totalExpenses) * 100, 1) : 0,
                    'color' => $category->category?->color ?? '#666666',
                    'icon' => $category->category?->icon ?? 'ShoppingBag'
                ];
            })
        ];

        return response()->json($stats);
    }

    private function getDateRange($type, $startDate = null, $endDate = null)
    {
        $now = Carbon::now();

        return match ($type) {
            'today' => [
                'start' => $now->startOfDay(),
                'end' => $now->clone()->endOfDay()
            ],
            'week' => [
                'start' => $now->startOfWeek(),
                'end' => $now->clone()->endOfWeek()
            ],
            'month' => [
                'start' => $now->startOfMonth(),
                'end' => $now->clone()->endOfMonth()
            ],
            'year' => [
                'start' => $now->startOfYear(),
                'end' => $now->clone()->endOfYear()
            ],
            'custom' => [
                'start' => Carbon::parse($startDate)->startOfDay(),
                'end' => Carbon::parse($endDate)->endOfDay()
            ],
            default => null
        };
    }

    private function calculateDailyAverage($query, $dateRange)
    {
        $totalAmount = $query->where('type', 'expense')->sum('amount');
        $days = Carbon::parse(time: $dateRange['start'])->diffInDays($dateRange['end']) + 1;
        return $days > 0 ? $totalAmount / $days : 0;
    }

    private function getMostExpensiveDay($query): mixed
    {
        return $query->where('type', 'expense')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(amount) as total'))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderByDesc('total')
            ->first();
    }

    private function getPreviousPeriodComparison($type, $currentStart)
    {
        $previousStart = match ($type) {
            'day' => Carbon::parse($currentStart)->subDay(),
            'week' => Carbon::parse($currentStart)->subWeek(),
            'month' => Carbon::parse($currentStart)->subMonth(),
            'year' => Carbon::parse($currentStart)->subYear(),
            default => Carbon::parse($currentStart)->subMonth()
        };

        $previousTotal = Transaction::query()
            ->where('user_id', auth()->id())
            ->where('type', 'expense')
            ->whereBetween('created_at', [
                $previousStart,
                Carbon::parse($currentStart)->subDay()
            ])
            ->sum('amount');

        $currentTotal = Transaction::query()
            ->where('user_id', auth()->id())
            ->where('type', 'expense')
            ->where('created_at', '>=', $currentStart)
            ->sum('amount');

        if ($previousTotal == 0)
            return 0;
        return round((($currentTotal - $previousTotal) / $previousTotal) * 100, 1);
    }

    private function calculateSavingsRate($query)
    {
        $income = $query->clone()->where('type', 'income')->sum('amount');
        $expenses = $query->clone()->where('type', 'expense')->sum('amount');

        if ($income == 0)
            return 0;

        $rate = round((($income - $expenses) / $income) * 100, 1);

        return max(0, $rate);
    }
    private function calculateBudgetUtilization($totalExpenses)
    {
        // Assuming you have a monthly budget set for the user
        $monthlyBudget = auth()->user()->monthly_budget ?? 30000; // Default budget
        return round(($totalExpenses / $monthlyBudget) * 100, 1);
    }

    private function getFinancialHealthStatus($query)
    {
        $savingsRate = $this->calculateSavingsRate($query);
        if ($savingsRate >= 20) {
            return ['status' => 'Good', 'color' => 'green'];
        } elseif ($savingsRate >= 10) {
            return ['status' => 'Fair', 'color' => 'yellow'];
        } else {
            return ['status' => 'Poor', 'color' => 'red'];
        }
    }

    public function yearlyComparison(Request $request)
    {
        $validated = $request->validate([
            'previous_year' => ['required', 'integer', 'min:2020', 'max:' . Carbon::now()->year],
            'current_year' => ['required', 'integer', 'min:2020', 'max:' . Carbon::now()->year]
        ]);
        if ($validated['previous_year'] && $validated['current_year']) {
            return response()->json($this->statsRepository->getYearlyComparison($validated['previous_year'], $validated['current_year']));
        }
        return response()->json([
            'error' => 'Invalid request'
        ], 400);
    }
}
