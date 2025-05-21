<?php

namespace App\Services;

use App\Data\GetTransactionFilterData;
use App\Enum\TransactionFilterEnum;
use App\Enums\TransactionTypeEnum;
use App\Models\Transaction;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
                    $query->whereBetween('transaction_date', [
                        $dateArr['start'],
                        $dateArr['end']
                    ]);
                }
            }

            return $query->latest('transaction_date')
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
            TransactionFilterEnum::YESTERDAY->value => [
                'start' => now()->subDay()->startOfDay(),
                'end' => now()->subDay()->endOfDay(),
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
                'filteredSummary' => $this->getSummary($userId, $dateRange),
                'totalIncome' => $this->getTotalIncome($userId),
                'totalExpense' => $this->getTotalExpenses($userId),
                'categories' => $this->getCategorySummary($userId)
            ];
        });
    }

    /**
     * Create new transaction
     */
    public function createTransaction($data)
    {
        $wallet = app(WalletService::class);
        $transaction = DB::transaction(function () use ($data,$wallet) {
            $transaction = Transaction::create($data);
            $wallet->syncWallet($transaction);
        });
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
    private function getSummary($userId, $dateRange)
    {
        return Transaction::where('user_id', $userId)
            ->whereBetween('transaction_date', [
                $dateRange['start'],
                $dateRange['end']
            ])
            // ->whereDate('transaction_date', Carbon::today())
            ->selectRaw('SUM(CASE WHEN type = "expense" THEN amount ELSE 0 END) as expense_total')
            ->selectRaw(expression: 'SUM(CASE WHEN type = "income" THEN amount ELSE 0 END) as income_total')
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

    private function getBalance(int $userId)
    {
        $totalExpense = $this->getTotalExpenses($userId);
        $totalIncome = $this->getTotalIncome($userId);
        return $totalIncome - $totalExpense;
    }

    private function getTotalExpenses($userId):float
    {
        return Transaction::where([
            'user_id' => $userId,
            'type' => TransactionTypeEnum::EXPENSE->value
        ])->sum('amount');
    }

    private function getTotalIncome($userId):float
    {
        return Transaction::where([
            'user_id' => $userId,
            'type' => TransactionTypeEnum::INCOME->value
        ])->sum('amount');
    }

    public function searchTransaction($dateFilter, $query)
    {
        // Get the base query builder
        $transactions = Transaction::with('category')
            ->where('user_id', auth()->id());

        // Apply date filter if provided
        if ($dateFilter) {
            $dateRange = $this->getFromFilter($dateFilter);
            $transactions->whereBetween('transaction_date', [
                $dateRange['start'],
                $dateRange['end']
            ]);
        }

        // Apply search query if provided
        if ($query) {
            $transactions->where(function ($q) use ($query) {
                $q->where('note', 'LIKE', "%{$query}%");
            });
        }

        // Return results ordered by latest transaction_date first
        return $transactions->latest('transaction_date')->get();
    }

    public function getCategoryTransactions(GetTransactionFilterData $data)
    {
        $query = Transaction::where([
            'user_id' => auth()->id(),
            'category_id' => $data->category
        ]);

        if ($data->start_date) {
            $query->whereBetween('transaction_date', [
                $data->start_date,
                $data->end_date
            ]);
        }

        return $query->get();
    }

    public function getActivityBarDataV2($userId, $period)
    {
        $first = Transaction::where('user_id', $userId)->orderBy('transaction_date')->first();
        $last = Transaction::where('user_id', $userId)->orderByDesc('transaction_date')->first();
        if (!$first || !$last) return [];
        $start = $first->transaction_date;
        $end = $last->transaction_date;
        $now = now();
        $result = [];
        if ($period === 'W') {
            $current = \Carbon\Carbon::parse($start)->startOfWeek();
            $final = \Carbon\Carbon::parse($end)->endOfWeek();
            while ($current <= $final) {
                $weekStart = $current->copy();
                $weekEnd = $current->copy()->endOfWeek();
                $income = Transaction::where('user_id', $userId)
                    ->whereBetween('transaction_date', [$weekStart, $weekEnd])
                    ->where('type', 'income')->sum('amount');
                $expense = Transaction::where('user_id', $userId)
                    ->whereBetween('transaction_date', [$weekStart, $weekEnd])
                    ->where('type', 'expense')->sum('amount');
                $label = $weekStart->format('d M') . ' - ' . $weekEnd->format('d M');
                $result[] = [
                    'label' => $label,
                    'income' => $income,
                    'expense' => $expense,
                    'start' => $weekStart->toDateString(),
                    'end' => $weekEnd->toDateString()
                ];
                $current->addWeek();
            }
        } elseif ($period === 'M') {
            $current = \Carbon\Carbon::parse($start)->startOfMonth();
            $final = \Carbon\Carbon::parse($end)->endOfMonth();
            while ($current <= $final) {
                $monthStart = $current->copy();
                $monthEnd = $current->copy()->endOfMonth();
                $income = Transaction::where('user_id', $userId)
                    ->whereBetween('transaction_date', [$monthStart, $monthEnd])
                    ->where('type', 'income')->sum('amount');
                $expense = Transaction::where('user_id', $userId)
                    ->whereBetween('transaction_date', [$monthStart, $monthEnd])
                    ->where('type', 'expense')->sum('amount');
                $label = $monthStart->format('M Y');
                $result[] = [
                    'label' => $label,
                    'income' => $income,
                    'expense' => $expense,
                    'start' => $monthStart->toDateString(),
                    'end' => $monthEnd->toDateString()
                ];
                $current->addMonth();
            }
        } elseif ($period === 'Y') {
            $current = \Carbon\Carbon::parse($start)->startOfYear();
            $final = \Carbon\Carbon::parse($end)->endOfYear();
            while ($current <= $final) {
                $yearStart = $current->copy();
                $yearEnd = $current->copy()->endOfYear();
                $income = Transaction::where('user_id', $userId)
                    ->whereBetween('transaction_date', [$yearStart, $yearEnd])
                    ->where('type', 'income')->sum('amount');
                $expense = Transaction::where('user_id', $userId)
                    ->whereBetween('transaction_date', [$yearStart, $yearEnd])
                    ->where('type', 'expense')->sum('amount');
                $label = $yearStart->format('Y');
                $result[] = [
                    'label' => $label,
                    'income' => $income,
                    'expense' => $expense,
                    'start' => $yearStart->toDateString(),
                    'end' => $yearEnd->toDateString()
                ];
                $current->addYear();
            }
        } elseif ($period === 'ALL') {
            $income = Transaction::where('user_id', $userId)
                ->whereBetween('transaction_date', [$start, $end])
                ->where('type', 'income')->sum('amount');
            $expense = Transaction::where('user_id', $userId)
                ->whereBetween('transaction_date', [$start, $end])
                ->where('type', 'expense')->sum('amount');
            $result[] = [
                'label' => 'All Time',
                'income' => $income,
                'expense' => $expense,
                'start' => $start,
                'end' => $end
            ];
        }
        return $result;
    }

    public function getTransactionsForBar($userId, $start, $end)
    {
        return Transaction::with('category')->where('user_id', $userId)
            ->whereBetween('created_at', [$start, $end])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function deleteTransaction(Transaction $transaction)
    {
        DB::transaction(function () use ($transaction) {
            $wallet = app(WalletService::class);
            $wallet->syncWallet($transaction);
            $transaction->delete();
        });

        $this->clearUserTransactionCache($transaction->user_id);
        return $transaction;
    }
}
