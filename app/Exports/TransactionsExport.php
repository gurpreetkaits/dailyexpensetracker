<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use Carbon\Carbon;

class TransactionsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithEvents
{
    protected $userId;
    protected $startDate;
    protected $endDate;
    protected $type;
    protected $transactions;
    protected $categoryColors = [];

    public function __construct($userId, $startDate = null, $endDate = null, $type = null)
    {
        $this->userId = $userId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->type = $type;
    }

    public function collection()
    {
        $query = Transaction::with(['category', 'wallet'])
            ->where('user_id', $this->userId)
            ->orderBy('category_id')
            ->orderBy('transaction_date', 'desc');

        if ($this->startDate) {
            $query->whereDate('transaction_date', '>=', $this->startDate);
        }

        if ($this->endDate) {
            $query->whereDate('transaction_date', '<=', $this->endDate);
        }

        if ($this->type && $this->type !== 'all') {
            $query->where('type', $this->type);
        }

        $this->transactions = $query->get();

        // Build category colors map
        foreach ($this->transactions as $transaction) {
            if ($transaction->category && $transaction->category->color) {
                $this->categoryColors[$transaction->category_id] = $transaction->category->color;
            }
        }

        return $this->transactions;
    }

    public function headings(): array
    {
        return [
            'Date',
            'Type',
            'Category',
            'Amount',
            'Note',
            'Wallet',
            'Reference Number',
        ];
    }

    public function map($transaction): array
    {
        $amountPrefix = $transaction->type === 'expense' ? '-' : '+';

        return [
            Carbon::parse($transaction->transaction_date)->format('Y-m-d'),
            ucfirst($transaction->type),
            $transaction->category?->name ?? 'Uncategorized',
            $amountPrefix . number_format($transaction->amount, 2),
            $transaction->note ?? '',
            $transaction->wallet?->name ?? '',
            $transaction->reference_number ?? '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '374151']
                ]
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $rowNumber = 2; // Start after header

                foreach ($this->transactions as $transaction) {
                    // Apply category color as left border or light background
                    if ($transaction->category && $transaction->category->color) {
                        $color = ltrim($transaction->category->color, '#');

                        // Apply light background tint of category color
                        $lightColor = $this->lightenColor($color, 0.85);

                        $sheet->getStyle("A{$rowNumber}:G{$rowNumber}")->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => $lightColor]
                            ]
                        ]);

                        // Apply category color as left border on category column
                        $sheet->getStyle("C{$rowNumber}")->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'color' => ['rgb' => $color]
                            ]
                        ]);
                    }

                    // Color the amount based on type
                    $amountColor = $transaction->type === 'expense' ? 'DC2626' : '16A34A';
                    $sheet->getStyle("D{$rowNumber}")->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'color' => ['rgb' => $amountColor]
                        ]
                    ]);

                    // Color the type column
                    $typeColor = $transaction->type === 'expense' ? 'FEE2E2' : 'DCFCE7';
                    $typeTextColor = $transaction->type === 'expense' ? 'DC2626' : '16A34A';
                    $sheet->getStyle("B{$rowNumber}")->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => $typeColor]
                        ],
                        'font' => [
                            'color' => ['rgb' => $typeTextColor]
                        ]
                    ]);

                    $rowNumber++;
                }

                // Add borders to all cells
                $lastRow = $rowNumber - 1;
                if ($lastRow >= 2) {
                    $sheet->getStyle("A1:G{$lastRow}")->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['rgb' => 'E5E7EB']
                            ]
                        ]
                    ]);
                }
            }
        ];
    }

    /**
     * Lighten a hex color
     */
    private function lightenColor($hex, $percent)
    {
        $hex = ltrim($hex, '#');

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $r = round($r + (255 - $r) * $percent);
        $g = round($g + (255 - $g) * $percent);
        $b = round($b + (255 - $b) * $percent);

        return sprintf('%02X%02X%02X', $r, $g, $b);
    }
}
