<?php

namespace App\Services;

use App\Data\GetTransactionFilterData;
use App\Enum\TransactionFilterEnum;
use App\Enums\TransactionTypeEnum;
use App\Models\Category;
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
            $query = Transaction::with(['category','wallet:id,name'])->where('user_id', $userId);

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

    /**
     * Get paginated user's transactions with caching
     * Paginated and filtered by date range
     */
    public function getPaginatedUserTransactions($userId, $filters)
    {
        $filter = $filters['filter'] ?? 'all';
        $page = $filters['page'] ?? 1;
        $perPage = $filters['per_page'] ?? 10;

        $cacheKey = "paginated_transactions_user_{$userId}_{$filter}_page_{$page}_perPage_{$perPage}_" . md5(json_encode($filters));

        return Cache::remember($cacheKey, $this->cacheTimeout, function () use ($userId, $filters) {
            $query = Transaction::with(['category','wallet:id,name,balance'])->where('user_id', $userId);

            // Apply date filter from predefined filters
            if (!empty($filters['filter']) && $filters['filter'] !== 'all') {
                $dateArr = $this->getFromFilter($filters['filter']);
                if ($dateArr) {
                    $query->whereBetween('created_at', [
                        $dateArr['start'],
                        $dateArr['end']
                    ]);
                }
            }

            // Apply transaction type filter
            if (!empty($filters['type']) && $filters['type'] !== 'all') {
                $query->where('type', $filters['type']);
            }

            // Apply wallet filter
            if (!empty($filters['wallet_id'])) {
                $query->where('wallet_id', $filters['wallet_id']);
            }

            // Apply category filter
            if (!empty($filters['category_id'])) {
                $query->where('category_id', $filters['category_id']);
            }

            // Apply search filter
            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function($q) use ($search) {
                    $q->where('note', 'like', "%{$search}%")
                      ->orWhere('amount', 'like', "%{$search}%")
                      ->orWhere('reference_number', 'like', "%{$search}%");
                });
            }

            // Apply custom date range
            if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
                $query->whereBetween('transaction_date', [
                    $filters['date_from'],
                    $filters['date_to']
                ]);
            } elseif (!empty($filters['date_from'])) {
                $query->where('transaction_date', '>=', $filters['date_from']);
            } elseif (!empty($filters['date_to'])) {
                $query->where('transaction_date', '<=', $filters['date_to']);
            }

            return $query->latest('created_at')
                ->paginate($filters['per_page'] ?? 10, ['*'], 'page', $filters['page'] ?? 1);
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
            $wallet->syncWallet($transaction,'create');
            return $transaction;
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
    public function getSummary($userId, $dateRange)
    {
        return Transaction::where('user_id', $userId)
            ->whereBetween('transaction_date', [
                $dateRange['start'],
                $dateRange['end']
            ])
//            ->whereNotIn('category_id', Category::WALLET_TRANSFER_ID)
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
        ])
//            ->whereNotIn('category_id', Category::WALLET_TRANSFER_ID)
            ->sum('amount');
    }

    private function getTotalIncome($userId):float
    {
        return Transaction::where([
            'user_id' => $userId,
            'type' => TransactionTypeEnum::INCOME->value
        ])
//            ->whereNotIn('category_id', Category::WALLET_TRANSFER_ID)
            ->sum('amount');
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

        // Return results ordered by latest created_at first
        return $transactions->latest('transaction_date')->get();
    }

    public function getCategoryTransactions(GetTransactionFilterData $data): \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
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

    public function getActivityBarDataV2($userId, $period): array
    {
        $first = Transaction::where('user_id', $userId)->orderBy('created_at')->first();
        $last = Transaction::where('user_id', $userId)->orderByDesc('created_at')->first();
        if (!$first || !$last) return [];
        $start = $first->created_at;
        $end = $last->created_at;
        $now = now();
        $result = [];
        if($period === 'D'){
            $dayStart = $now->copy()->startOfDay();
            $dayEnd = $now->copy()->endOfDay();
            $income = Transaction::where('user_id', $userId)
                ->whereBetween('transaction_date', [$dayStart, $dayEnd])
                ->where('type', 'income')->sum('amount');
            $expense = Transaction::where('user_id', $userId)
                ->whereBetween('transaction_date', [$dayStart, $dayEnd])
                ->where('type', 'expense')->sum('amount');
            $label = $dayStart->format('d M');
            $result[] = [
                'label' => $label,
                'income' => $income,
                'expense' => $expense,
                'start' => $dayStart->toDateString(),
                'end' => $dayEnd->toDateString()
            ];
        } elseif ($period === 'W') {
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

    public function getTransactionsForBar($userId, $start, $end): \Illuminate\Database\Eloquent\Collection
    {
        return Transaction::with(['category', 'wallet:id,name'])
            ->where('user_id', $userId)
            ->whereDate('transaction_date', '>=', $start)
            ->whereDate('transaction_date', '<=', $end)
            ->orderBy('transaction_date', 'desc')
            ->get();
    }

    public function getDailyBarData($userId, $days = 60): array
    {
        $result = [];
        $today = Carbon::today();

        // Fetch all transactions for the date range in one query for better performance
        $startDate = $today->copy()->subDays($days - 1)->toDateString();
        $endDate = $today->toDateString();

        $transactions = Transaction::where('user_id', $userId)
            ->whereDate('transaction_date', '>=', $startDate)
            ->whereDate('transaction_date', '<=', $endDate)
            ->selectRaw('DATE(transaction_date) as date, type, SUM(amount) as total')
            ->groupBy('date', 'type')
            ->get()
            ->groupBy('date');

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i);
            $dateString = $date->toDateString();

            $dayData = $transactions->get($dateString);
            $income = 0;
            $expense = 0;

            if ($dayData) {
                foreach ($dayData as $item) {
                    if ($item->type === 'income') {
                        $income = (float) $item->total;
                    } else if ($item->type === 'expense') {
                        $expense = (float) $item->total;
                    }
                }
            }

            $result[] = [
                'date' => $dateString,
                'dayName' => $date->format('D'),
                'dayNum' => $date->format('j'),
                'month' => $date->format('M'),
                'income' => $income,
                'expense' => $expense,
            ];
        }

        return $result;
    }

    public function deleteTransaction(Transaction $transaction)
    {
        DB::transaction(function () use ($transaction) {
            $wallet = app(WalletService::class);
            $wallet->syncWallet($transaction,'delete');
            $transaction->delete(); // This will now be a soft delete
        });

        $this->clearUserTransactionCache($transaction->user_id);
        return $transaction;
    }

    public function restoreTransaction(Transaction $transaction)
    {
        DB::transaction(function () use ($transaction) {
            $transaction->restore();
            $wallet = app(WalletService::class);
            $wallet->syncWallet($transaction,'create'); // Re-sync wallet balance
        });

        $this->clearUserTransactionCache($transaction->user_id);
        return $transaction;
    }

    public function forceDeleteTransaction(Transaction $transaction)
    {
        DB::transaction(function () use ($transaction) {
            $wallet = app(WalletService::class);
            $wallet->syncWallet($transaction,'delete');
            $transaction->forceDelete(); // Permanently delete
        });

        $this->clearUserTransactionCache($transaction->user_id);
        return $transaction;
    }

    public function getTrashedTransactions($userId)
    {
        return Transaction::with(['category','wallet:id,name'])
            ->where('user_id', $userId)
            ->onlyTrashed()
            ->latest('deleted_at')
            ->get();
    }
}
