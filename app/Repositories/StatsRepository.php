<?php

namespace App\Repositories;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class StatsRepository
{
    public function getYearlyComparison($previousYear, $currentYear)
    {
        $months = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun',
            7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec'
        ];

        // Previous year
        $previousYearData = Transaction::where('user_id', auth()->id())
            ->where('type', 'expense')
            ->whereYear('created_at', $previousYear)
            ->selectRaw('MONTH(created_at) as month, SUM(amount) as amount')
            ->groupBy('month')
            ->pluck('amount', 'month')
            ->toArray();

        $previousYearTransactions = [];
        foreach ($months as $num => $name) {
            $previousYearTransactions[] = [
                'month' => $name,
                'amount' => isset($previousYearData[$num]) ? (float)$previousYearData[$num] : 0
            ];
        }

        // Current year
        $currentYearData = Transaction::where('user_id', auth()->id())
            ->where('type', 'expense')
            ->whereYear('created_at', $currentYear)
            ->selectRaw('MONTH(created_at) as month, SUM(amount) as amount')
            ->groupBy('month')
            ->pluck('amount', 'month')
            ->toArray();

        $currentYearTransactions = [];
        foreach ($months as $num => $name) {
            $currentYearTransactions[] = [
                'month' => $name,
                'amount' => isset($currentYearData[$num]) ? (float)$currentYearData[$num] : 0
            ];
        }

        return [
            'previous_year' => $previousYearTransactions,
            'current_year' => $currentYearTransactions
        ];
    }
}