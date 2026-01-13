<?php

namespace App\Http\Controllers;

use App\Exports\TransactionsExport;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Response;

class ExportController extends Controller
{
    public function exportTransactions(Request $request)
    {
        $request->validate([
            'format' => 'required|in:xlsx,csv,pdf',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'type' => 'nullable|in:income,expense,all',
        ]);

        $userId = auth()->id();
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

        // Return as downloadable HTML (can be printed as PDF from browser)
        return Response::make($html, 200, [
            'Content-Type' => 'text/html',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.html"',
        ]);
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
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #10b981; padding-bottom: 20px; }
        .header h1 { color: #10b981; font-size: 24px; margin-bottom: 5px; }
        .header p { color: #666; }
        .summary { display: table; width: 100%; margin-bottom: 30px; }
        .summary-box { display: table-cell; text-align: center; padding: 15px 30px; border-radius: 8px; width: 33%; }
        .summary-box.income { background: #dcfce7; }
        .summary-box.expense { background: #fee2e2; }
        .summary-box.net { background: #e0e7ff; }
        .summary-box h3 { font-size: 11px; color: #666; margin-bottom: 5px; text-transform: uppercase; }
        .summary-box p { font-size: 18px; font-weight: bold; }
        .summary-box.income p { color: #16a34a; }
        .summary-box.expense p { color: #dc2626; }
        .summary-box.net p { color: #4f46e5; }
        .category-summary { margin-bottom: 30px; }
        .category-summary h2 { font-size: 16px; margin-bottom: 15px; color: #374151; }
        .category-grid { display: flex; flex-wrap: wrap; gap: 10px; }
        .category-card { padding: 12px 16px; border-radius: 8px; border-left: 4px solid; background: #f9fafb; min-width: 180px; }
        .category-card h4 { font-size: 13px; margin-bottom: 8px; }
        .category-card .stats { display: flex; gap: 15px; font-size: 11px; }
        .category-card .stat-income { color: #16a34a; }
        .category-card .stat-expense { color: #dc2626; }
        .category-section { margin-bottom: 25px; page-break-inside: avoid; }
        .category-header { padding: 10px 15px; border-radius: 8px 8px 0 0; margin-bottom: 0; display: flex; justify-content: space-between; align-items: center; }
        .category-header h3 { font-size: 14px; color: white; }
        .category-header .totals { font-size: 11px; color: rgba(255,255,255,0.9); }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f1f5f9; padding: 10px 8px; text-align: left; font-weight: 600; font-size: 11px; }
        td { padding: 8px; border-bottom: 1px solid #e2e8f0; font-size: 11px; }
        tr:hover { background: #f8fafc; }
        .type-income { color: #16a34a; font-weight: 500; }
        .type-expense { color: #dc2626; font-weight: 500; }
        .amount-income { color: #16a34a; font-weight: 600; }
        .amount-expense { color: #dc2626; font-weight: 600; }
        .footer { margin-top: 30px; text-align: center; color: #999; font-size: 10px; border-top: 1px solid #e2e8f0; padding-top: 20px; }
        @media print {
            body { padding: 0; }
            .category-section { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Daily Expense Tracker</h1>
        <p>Transaction Report - ' . $dateRange . '</p>
        <p style="font-size: 10px; margin-top: 5px;">Generated on ' . now()->format('M d, Y \a\t h:i A') . '</p>
    </div>

    <div class="summary">
        <div class="summary-box income">
            <h3>Total Income</h3>
            <p>$' . number_format($totalIncome, 2) . '</p>
        </div>
        <div class="summary-box expense">
            <h3>Total Expense</h3>
            <p>$' . number_format($totalExpense, 2) . '</p>
        </div>
        <div class="summary-box net">
            <h3>Net Balance</h3>
            <p>$' . number_format($totalIncome - $totalExpense, 2) . '</p>
        </div>
    </div>

    <div class="category-summary">
        <h2>Category Overview</h2>
        <div class="category-grid">';

        foreach ($categoryTotals as $categoryName => $totals) {
            $html .= '
            <div class="category-card" style="border-left-color: ' . $totals['color'] . ';">
                <h4 style="color: ' . $totals['color'] . ';">' . $categoryName . '</h4>
                <div class="stats">
                    <span class="stat-income">+$' . number_format($totals['income'], 2) . '</span>
                    <span class="stat-expense">-$' . number_format($totals['expense'], 2) . '</span>
                    <span style="color: #6b7280;">(' . $totals['count'] . ')</span>
                </div>
            </div>';
        }

        $html .= '
        </div>
    </div>';

        // Transactions grouped by category
        foreach ($groupedTransactions as $categoryName => $transactions) {
            $category = $transactions->first()->category;
            $color = $category?->color ?? '#6B7280';
            $catIncome = $transactions->where('type', 'income')->sum('amount');
            $catExpense = $transactions->where('type', 'expense')->sum('amount');

            $html .= '
    <div class="category-section">
        <div class="category-header" style="background: ' . $color . ';">
            <h3>' . $categoryName . '</h3>
            <div class="totals">
                Income: $' . number_format($catIncome, 2) . ' | Expense: $' . number_format($catExpense, 2) . ' | ' . $transactions->count() . ' transactions
            </div>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Note</th>
                    <th>Wallet</th>
                </tr>
            </thead>
            <tbody>';

            foreach ($transactions as $t) {
                $typeClass = $t->type === 'income' ? 'type-income' : 'type-expense';
                $amountClass = $t->type === 'income' ? 'amount-income' : 'amount-expense';
                $amountPrefix = $t->type === 'income' ? '+' : '-';

                $html .= '
                <tr>
                    <td>' . Carbon::parse($t->transaction_date)->format('M d, Y') . '</td>
                    <td class="' . $typeClass . '">' . ucfirst($t->type) . '</td>
                    <td class="' . $amountClass . '">' . $amountPrefix . '$' . number_format($t->amount, 2) . '</td>
                    <td>' . ($t->note ?? '-') . '</td>
                    <td>' . ($t->wallet?->name ?? '-') . '</td>
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
