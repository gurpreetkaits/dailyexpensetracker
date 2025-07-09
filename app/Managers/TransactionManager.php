<?php

namespace App\Managers;

use App\Services\TransactionService;

class TransactionManager
{
    protected TransactionService $transaction;
    public function __construct()
    {
        $this->transaction = app(TransactionService::class);
    }

    public function getWeeklySummary(int $userId): array
    {
        $startOfLastWeek = now()->startOfWeek()->subWeek()->toDateString();
        $endOfLastWeek = now()->startOfWeek()->subDay()->toDateString();

        $dateRange = [
            'start' => $startOfLastWeek,
            'end' => $endOfLastWeek
        ];

        $data = $this->transaction->getSummary($userId,$dateRange);
        return [
            'totals' => [
                'income' => $data->income_total,
                'expense' => $data->expense_total,
            ]
        ];
    }
}
