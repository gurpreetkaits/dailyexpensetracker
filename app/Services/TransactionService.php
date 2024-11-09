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
                $dateArr = match ($filter) {
                    TransactionFilterEnum::TODAY => [
                        'start' => now()->startOfDay(),
                        'end' => now()->endOfDay(),
                    ],
                    TransactionFilterEnum::MONTHLY => [
                        'start' => now()->startOfMonth(),
                        'end' => now()->endOfMonth(),
                    ],
                    TransactionFilterEnum::WEEKLY => [
                        'start' => now()->startOfWeek(),
                        'end' => now()->endOfWeek(),
                    ],
                    TransactionFilterEnum::YEARLY => [
                        'start' => now()->startOfYear(),
                        'end' => now()->endOfYear(),
                    ],
                    TransactionFilterEnum::ALL => null,
                    default => null
                };
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

    /**
     * Get transaction summary (totals by type)
     */
    public function getTransactionSummary($userId)
    {
        $cacheKey = "transaction_summary_user_{$userId}_" . date('Y_m');

        return Cache::remember($cacheKey, $this->cacheTimeout, function () use ($userId) {
            $currentMonth = Carbon::now();

            return [
                'day' => $this->getDaySummary($userId),
                'week' => $this->getWeekSummary($userId),
                'month' => $this->getMonthSummary($userId),
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
    private function getDaySummary($userId)
    {
        return Transaction::where('user_id', $userId)
            ->whereDate('transaction_date', Carbon::today())
            ->selectRaw('SUM(CASE WHEN type = "expense" THEN amount ELSE 0 END) as expense_total')
            ->selectRaw('SUM(CASE WHEN type = "income" THEN amount ELSE 0 END) as income_total')
            ->first();
    }

    /**
     * Get weekly summary
     */
    private function getWeekSummary($userId)
    {
        return Transaction::where('user_id', $userId)
            ->whereBetween('transaction_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->selectRaw('SUM(CASE WHEN type = "expense" THEN amount ELSE 0 END) as expense_total')
            ->selectRaw('SUM(CASE WHEN type = "income" THEN amount ELSE 0 END) as income_total')
            ->first();
    }

    /**
     * Get monthly summary
     */
    private function getMonthSummary($userId)
    {
        return Transaction::where('user_id', $userId)
            ->whereYear('transaction_date', Carbon::now()->year)
            ->whereMonth('transaction_date', Carbon::now()->month)
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