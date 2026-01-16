<?php

namespace App\Http\Controllers;

use App\Exports\TransactionsExport;
use App\Models\ExportUsage;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportController extends Controller
{
    public function getExportStatus()
    {
        $user = auth()->user();
        $hasActiveSubscription = $user->activeSubscription !== null;

        if ($hasActiveSubscription) {
            return response()->json([
                'unlimited' => true,
                'remaining' => -1,
                'used' => 0,
                'limit' => -1,
            ]);
        }

        $remaining = ExportUsage::getRemainingExports($user->id);
        $used = 5 - $remaining;

        return response()->json([
            'unlimited' => false,
            'remaining' => $remaining,
            'used' => $used,
            'limit' => 5,
        ]);
    }

    public function exportTransactions(Request $request)
    {
        $request->validate([
            'format' => 'required|in:xlsx,csv,pdf',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'type' => 'nullable|in:income,expense,all',
        ]);

        $user = auth()->user();
        $userId = $user->id;
        $hasActiveSubscription = $user->activeSubscription !== null;

        // Check export limit for free users
        if (!$hasActiveSubscription) {
            if (!ExportUsage::canExport($userId)) {
                return response()->json([
                    'error' => 'Export limit reached',
                    'message' => 'You have used all 5 exports for this year. Upgrade to Pro for unlimited exports.',
                    'remaining' => 0,
                ], 403);
            }

            // Increment usage
            ExportUsage::incrementUsage($userId);
        }

        $format = $request->input('format');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $type = $request->input('type');

        $filename = 'transactions_' . now()->format('Y-m-d_His');

        if ($format === 'pdf') {
            return $this->exportToPdf($userId, $startDate, $endDate, $type, $filename);
        }

        $export = new TransactionsExport($userId, $startDate, $endDate, $type);

        if ($format === 'csv') {
            return Excel::download($export, $filename . '.csv', \Maatwebsite\Excel\Excel::CSV);
        }

        return Excel::download($export, $filename . '.xlsx', \Maatwebsite\Excel\Excel::XLSX);
    }

    protected function exportToPdf($userId, $startDate, $endDate, $type, $filename)
    {
        $query = Transaction::with(['category', 'wallet'])
            ->where('user_id', $userId)
            ->orderBy('category_id')
            ->orderBy('transaction_date', 'desc');

        if ($startDate) {
            $query->whereDate('transaction_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('transaction_date', '<=', $endDate);
        }

        if ($type && $type !== 'all') {
            $query->where('type', $type);
        }

        $transactions = $query->get();

        // Group transactions by category
        $groupedTransactions = $transactions->groupBy(function($t) {
            return $t->category?->name ?? 'Uncategorized';
        });

        // Calculate totals
        $totalIncome = $transactions->where('type', 'income')->sum('amount');
        $totalExpense = $transactions->where('type', 'expense')->sum('amount');

        // Calculate category totals
        $categoryTotals = [];
        foreach ($groupedTransactions as $categoryName => $items) {
            $category = $items->first()->category;
            $categoryTotals[$categoryName] = [
                'color' => $category?->color ?? '#6B7280',
                'income' => $items->where('type', 'income')->sum('amount'),
                'expense' => $items->where('type', 'expense')->sum('amount'),
                'count' => $items->count()
            ];
        }

        // Generate HTML for PDF
        $html = $this->generatePdfHtml($groupedTransactions, $totalIncome, $totalExpense, $startDate, $endDate, $categoryTotals);

        // Generate PDF using dompdf
        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download($filename . '.pdf');
    }

    protected function generatePdfHtml($groupedTransactions, $totalIncome, $totalExpense, $startDate, $endDate, $categoryTotals)
    {
        $dateRange = '';
        if ($startDate && $endDate) {
            $dateRange = Carbon::parse($startDate)->format('M d, Y') . ' - ' . Carbon::parse($endDate)->format('M d, Y');
        } elseif ($startDate) {
            $dateRange = 'From ' . Carbon::parse($startDate)->format('M d, Y');
        } elseif ($endDate) {
            $dateRange = 'Until ' . Carbon::parse($endDate)->format('M d, Y');
        } else {
            $dateRange = 'All Time';
        }

        $totalCount = collect($categoryTotals)->sum('count');

        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Transaction Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #333; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #10b981; padding-bottom: 15px; }
        .header h1 { color: #10b981; font-size: 20px; margin-bottom: 5px; }
        .header p { color: #666; font-size: 10px; }
        .summary-table { width: 100%; margin-bottom: 20px; border-collapse: separate; border-spacing: 8px; }
        .summary-table td { text-align: center; padding: 12px; border-radius: 6px; width: 33%; }
        .summary-table .income { background: #dcfce7; }
        .summary-table .expense { background: #fee2e2; }
        .summary-table .net { background: #e0e7ff; }
        .summary-table h3 { font-size: 9px; color: #666; margin-bottom: 5px; text-transform: uppercase; font-weight: normal; }
        .summary-table .amount { font-size: 14px; font-weight: bold; }
        .summary-table .income .amount { color: #16a34a; }
        .summary-table .expense .amount { color: #dc2626; }
        .summary-table .net .amount { color: #4f46e5; }
        .category-overview { margin-bottom: 20px; }
        .category-overview h2 { font-size: 12px; margin-bottom: 10px; color: #374151; }
        .category-cards { width: 100%; border-collapse: separate; border-spacing: 6px; }
        .category-cards td { padding: 8px 10px; background: #f9fafb; border-left: 3px solid; vertical-align: top; }
        .category-cards .cat-name { font-size: 10px; font-weight: bold; margin-bottom: 4px; }
        .category-cards .cat-stats { font-size: 9px; }
        .category-cards .stat-income { color: #16a34a; }
        .category-cards .stat-expense { color: #dc2626; }
        .category-section { margin-bottom: 15px; page-break-inside: avoid; }
        .category-header-table { width: 100%; margin-bottom: 0; }
        .category-header-table td { padding: 8px 12px; color: white; font-size: 10px; }
        .category-header-table .cat-title { font-size: 11px; font-weight: bold; }
        .category-header-table .cat-totals { text-align: right; font-size: 9px; }
        .transactions-table { width: 100%; border-collapse: collapse; }
        .transactions-table th { background: #f1f5f9; padding: 6px 8px; text-align: left; font-weight: bold; font-size: 9px; border-bottom: 1px solid #e2e8f0; }
        .transactions-table td { padding: 6px 8px; border-bottom: 1px solid #e2e8f0; font-size: 9px; }
        .type-income { color: #16a34a; }
        .type-expense { color: #dc2626; }
        .amount-income { color: #16a34a; font-weight: bold; }
        .amount-expense { color: #dc2626; font-weight: bold; }
        .footer { margin-top: 20px; text-align: center; color: #999; font-size: 8px; border-top: 1px solid #e2e8f0; padding-top: 15px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Daily Expense Tracker</h1>
        <p>Transaction Report - ' . $dateRange . '</p>
        <p style="margin-top: 3px;">Generated on ' . now()->format('M d, Y \a\t h:i A') . '</p>
    </div>

    <table class="summary-table">
        <tr>
            <td class="income">
                <h3>Total Income</h3>
                <div class="amount">' . number_format($totalIncome, 2) . '</div>
            </td>
            <td class="expense">
                <h3>Total Expense</h3>
                <div class="amount">' . number_format($totalExpense, 2) . '</div>
            </td>
            <td class="net">
                <h3>Net Balance</h3>
                <div class="amount">' . number_format($totalIncome - $totalExpense, 2) . '</div>
            </td>
        </tr>
    </table>

    <div class="category-overview">
        <h2>Category Overview</h2>
        <table class="category-cards">
            <tr>';

        $colCount = 0;
        foreach ($categoryTotals as $categoryName => $totals) {
            if ($colCount > 0 && $colCount % 3 === 0) {
                $html .= '</tr><tr>';
            }
            $html .= '
                <td style="border-left-color: ' . $totals['color'] . ';">
                    <div class="cat-name" style="color: ' . $totals['color'] . ';">' . htmlspecialchars($categoryName) . '</div>
                    <div class="cat-stats">
                        <span class="stat-income">+' . number_format($totals['income'], 2) . '</span> |
                        <span class="stat-expense">-' . number_format($totals['expense'], 2) . '</span>
                        <span style="color: #6b7280;">(' . $totals['count'] . ')</span>
                    </div>
                </td>';
            $colCount++;
        }

        // Fill remaining cells if needed
        $remaining = 3 - ($colCount % 3);
        if ($remaining < 3) {
            for ($i = 0; $i < $remaining; $i++) {
                $html .= '<td style="border-left-color: transparent; background: transparent;"></td>';
            }
        }

        $html .= '
            </tr>
        </table>
    </div>';

        // Transactions grouped by category
        foreach ($groupedTransactions as $categoryName => $transactions) {
            $category = $transactions->first()->category;
            $color = $category?->color ?? '#6B7280';
            $catIncome = $transactions->where('type', 'income')->sum('amount');
            $catExpense = $transactions->where('type', 'expense')->sum('amount');

            $html .= '
    <div class="category-section">
        <table class="category-header-table" style="background: ' . $color . ';">
            <tr>
                <td class="cat-title">' . htmlspecialchars($categoryName) . '</td>
                <td class="cat-totals">Income: ' . number_format($catIncome, 2) . ' | Expense: ' . number_format($catExpense, 2) . ' | ' . $transactions->count() . ' transactions</td>
            </tr>
        </table>
        <table class="transactions-table">
            <thead>
                <tr>
                    <th style="width: 18%;">Date</th>
                    <th style="width: 12%;">Type</th>
                    <th style="width: 15%;">Amount</th>
                    <th style="width: 35%;">Note</th>
                    <th style="width: 20%;">Wallet</th>
                </tr>
            </thead>
            <tbody>';

            foreach ($transactions as $t) {
                $typeClass = $t->type === 'income' ? 'type-income' : 'type-expense';
                $amountClass = $t->type === 'income' ? 'amount-income' : 'amount-expense';

                $html .= '
                <tr>
                    <td>' . Carbon::parse($t->transaction_date)->format('M d, Y') . '</td>
                    <td class="' . $typeClass . '">' . ucfirst($t->type) . '</td>
                    <td class="' . $amountClass . '">' . number_format($t->amount, 2) . '</td>
                    <td>' . htmlspecialchars($t->note ?? '-') . '</td>
                    <td>' . htmlspecialchars($t->wallet?->name ?? '-') . '</td>
                </tr>';
            }

            $html .= '
            </tbody>
        </table>
    </div>';
        }

        $html .= '
    <div class="footer">
        <p>Daily Expense Tracker - Your Personal Finance Manager</p>
        <p>Total Transactions: ' . $totalCount . ' | Categories: ' . count($categoryTotals) . '</p>
    </div>
</body>
</html>';

        return $html;
    }
}
