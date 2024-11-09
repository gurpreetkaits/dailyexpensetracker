<?php

namespace App\Services;

use App\Enum\TransactionFilterEnum;
use App\Models\Transaction;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class TransactionService
{
    private $cacheTimeout = 2; // 1 hour cache

    /**
     * Get user's transactions with caching
     * Paginated and filtered by date range
     */
    public function getUserTransactions($userId, $filter)
    {
        $cacheKey = "transactions_user_{$userId}_" . time();

        return Cache::remember($cacheKey, $this->cacheTimeout, function () use ($userId, $filter) {
            $query = Transaction::with('category')->where('user_id', $userId);

            if ($filter) {
                $dateArr = $this->getFromFilter($filter);
                if ($dateArr) {
                    $query->whereBetween('created_at', [
                        $dateArr['start'],
                        $dateArr['end']
                    ]);
                }
            }

            return $query->latest('created_at')
                ->get();
        });
    }
    private function getFromFilter(string $filter): array
    {
        return match ($filter) {
            TransactionFilterEnum::TODAY->value => [
                'start' => now()->startOfDay(),
                'end' => now()->endOfDay(),
            ],
            TransactionFilterEnum::MONTHLY->value => [
                'start' => now()->startOfMonth(),
                'end' => now()->endOfMonth(),
            ],
            TransactionFilterEnum::WEEKLY->value => [
                'start' => now()->startOfWeek(),
                'end' => now()->endOfWeek(),
            ],
            TransactionFilterEnum::YEARLY->value => [
                'start' => now()->startOfYear(),
                'end' => now()->endOfYear(),
            ],
            default => [
                'start' => auth()->user()->created_at,
                'end' => now()->endOfDay(),
            ]
        };
    }
    /**
     * Get transaction summary (totals by type)
     */
    public function getTransactionSummary($userId, $filter)
    {
        $cacheKey = "transaction_summary_user_{$userId}_" . date('Y_m');

        return Cache::remember($cacheKey, $this->cacheTimeout, function () use ($userId, $filter) {
            $dateRange = $this->getFromFilter($filter);
            return [
                'day' => $this->getDaySummary($userId, $dateRange),
                'week' => $this->getWeekSummary($userId, $dateRange),
                'month' => $this->getMonthSummary($userId, $dateRange),
                'categories' => $this->getCategorySummary($userId)
            ];
        });
    }

    /**
     * Create new transaction
     */
    public function createTransaction($data)
    {
        $transaction = Transaction::create($data);

        // Clear related caches
        $this->clearUserTransactionCache($data['user_id']);

        return $transaction;
    }

    /**
     * Clear user's transaction cache
     */
    public function clearUserTransactionCache($userId)
    {
        Cache::forget("transactions_user_{$userId}_" . date('m') . "_" . date('Y'));
        Cache::forget("transaction_summary_user_{$userId}_" . date('Y_m'));
    }

    /**
     * Get daily summary
     */
    private function getDaySummary($userId, $dateRange)
    {
        return Transaction::where('user_id', $userId)
            ->whereBetween('created_at', [
                $dateRange['start'],
                $dateRange['end']
            ])
            // ->whereDate('transaction_date', Carbon::today())
            ->selectRaw('SUM(CASE WHEN type = "expense" THEN amount ELSE 0 END) as expense_total')
            ->selectRaw(expression: 'SUM(CASE WHEN type = "income" THEN amount ELSE 0 END) as income_total')
            ->first();
    }

    /**
     * Get weekly summary
     */
    private function getWeekSummary($userId, $dateRange)
    {
        return Transaction::where('user_id', $userId)
            ->whereBetween('created_at', [
                $dateRange['start'],
                $dateRange['end']
            ])
            ->selectRaw('SUM(CASE WHEN type = "expense" THEN amount ELSE 0 END) as expense_total')
            ->selectRaw('SUM(CASE WHEN type = "income" THEN amount ELSE 0 END) as income_total')
            ->first();
    }

    /**
     * Get monthly summary
     */
    private function getMonthSummary($userId,$dateRange)
    {
        return Transaction::where('user_id', $userId)
            ->whereBetween('created_at', [
                $dateRange['start'],
                $dateRange['end']
            ])
            ->selectRaw('SUM(CASE WHEN type = "expense" THEN amount ELSE 0 END) as expense_total')
            ->selectRaw('SUM(CASE WHEN type = "income" THEN amount ELSE 0 END) as income_total')
            ->first();
    }

    /**
     * Get category summary
     */
    private function getCategorySummary($userId)
    {
        return Transaction::where('user_id', $userId)
            ->whereYear('transaction_date', Carbon::now()->year)
            ->whereMonth('transaction_date', Carbon::now()->month)
            ->selectRaw('category, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('category')
            ->get();
    }
}