<?php

namespace App\Services;

use App\Models\ImportMapping;
use App\Models\Transaction;
use App\Models\Category;
use App\Models\Wallet;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class ImportService
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function getFileHeadings($file)
    {
        $data = Excel::toArray([], $file);

        if (empty($data) || empty($data[0])) {
            throw new \Exception('No data found in file');
        }

        // Return the first row as headers
        return $data[0][0] ?? [];
    }

    public function saveMapping($userId, $mappingName, $mappings)
    {
        return ImportMapping::create([
            'user_id' => $userId,
            'name' => $mappingName,
            'mappings' => json_encode($mappings)
        ]);
    }

    public function getUserMappings($userId)
    {
        return ImportMapping::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($mapping) {
                return [
                    'id' => $mapping->id,
                    'name' => $mapping->name,
                    'mappings' => json_decode($mapping->mappings, true),
                    'created_at' => $mapping->created_at
                ];
            });
    }

    public function deleteMapping($userId, $mappingId)
    {
        $mapping = ImportMapping::where('user_id', $userId)
            ->where('id', $mappingId)
            ->firstOrFail();

        $mapping->delete();
    }

    public function importTransactions($userId, $filePath, $mappings): array
    {
        $fullPath = storage_path('app/private/' . $filePath);

        if (!file_exists($fullPath)) {
            throw new \Exception('File not found at ');
        }

        // Read the Excel file
        $data = Excel::toArray([], $fullPath);

        if (empty($data) || empty($data[0])) {
            throw new \Exception('No data found in file');
        }

        $rows = $data[0];
        $headers = array_shift($rows); // Remove header row

        // Prepare mappings - they're already in the correct format
        $fieldMappings = $mappings;

        $importedCount = 0;
        $failedCount = 0;
        $errors = [];

        // Get selected wallet or default wallet
        $walletId = null;
        foreach ($fieldMappings as $mapping) {
            if ($mapping['field'] === 'wallet' && isset($mapping['wallet_id'])) {
                $walletId = $mapping['wallet_id'];
                break;
            }
        }

        $selectedWallet = null;
        if ($walletId) {
            $selectedWallet = Wallet::where('user_id', $userId)->where('id', $walletId)->first();
        }
        
        if (!$selectedWallet) {
            $selectedWallet = Wallet::where('user_id', $userId)->first();
        }

        if (!$selectedWallet) {
            throw new \Exception('No wallet found for user');
        }

        // Get user's categories for matching
        $categories = Category::where('user_id', $userId)->get();

        // Process each row
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 because we removed header and array is 0-indexed

            try {
                // Create associative array from row data
                $rowData = array_combine($headers, $row);

                // Extract data based on mappings
                $transactionData = $this->extractTransactionData($rowData, $fieldMappings, $userId, $categories, $selectedWallet);

                if ($transactionData) {
                    // Check for duplicate reference number
                    if (!empty($transactionData['reference_number'])) {
                        $exists = Transaction::where('user_id', $userId)
                            ->where('reference_number', $transactionData['reference_number'])
                            ->exists();

                        if ($exists) {
                            $failedCount++;
                            $errors[] = "Row {$rowNumber}: Duplicate reference number";
                            continue;
                        }
                    }

                    // Check for duplicate transaction (same user_id, amount, date, description)
                    $duplicateExists = Transaction::where('user_id', $userId)
                        ->where('amount', $transactionData['amount'])
                        ->where('transaction_date', $transactionData['transaction_date'])
                        ->where('note', $transactionData['note'])
                        ->exists();

                    if ($duplicateExists) {
                        $failedCount++;
                        $errors[] = "Row {$rowNumber}: Duplicate transaction - same amount, date, and description already exists";
                        continue;
                    }

                    // Create transaction
                    $this->transactionService->createTransaction($transactionData);
                    $importedCount++;
                } else {
                    // Row was skipped (empty required fields) - don't count as error
                    continue;
                }
            } catch (\Exception $e) {
                $failedCount++;
                $errors[] = "Row {$rowNumber}: " . $e->getMessage();
            }
        }

        // Clean up file
        Storage::disk('local')->delete($filePath);

        return [
            'imported_count' => $importedCount,
            'failed_count' => $failedCount,
            'errors' => $errors
        ];
    }

    private function extractTransactionData($rowData, $fieldMappings, $userId, $categories, $selectedWallet)
    {
        $data = [
            'user_id' => $userId,
            'wallet_id' => $selectedWallet->id
        ];

        foreach ($fieldMappings as $mapping) {
            $field = $mapping['field'];
            $columnName = $mapping['column'] ?? '';

            if ($field === 'type') {
                // Handle type mapping
                if (isset($mapping['useMapping']) && $mapping['useMapping'] && !empty($columnName)) {
                    // Use column mapping
                    if (isset($rowData[$columnName])) {
                        $data['type'] = $this->parseType($rowData[$columnName]);
                    }
                } elseif (isset($mapping['defaultType']) && !empty($mapping['defaultType'])) {
                    // Use default type
                    $data['type'] = $mapping['defaultType'];
                }
            } elseif ($field === 'category') {
                // Handle category mapping
                $mode = $mapping['mode'] ?? 'default';

                if ($mode === 'mapping' && !empty($columnName) && isset($rowData[$columnName])) {
                    // Use column mapping - create category if not exists
                    $data['category_id'] = $this->findOrCreateCategoryId($rowData[$columnName], $userId);
                } elseif ($mode === 'default' && isset($mapping['defaultCategoryId']) && !empty($mapping['defaultCategoryId'])) {
                    // Use default category
                    $data['category_id'] = $mapping['defaultCategoryId'];
                } elseif ($mode === 'ai') {
                    // Mark for AI processing
                    $data['needs_ai'] = true;
                }
            } elseif (!empty($columnName) && isset($rowData[$columnName])) {
                $value = $rowData[$columnName];

                switch ($field) {
                    case 'transaction_date':
                        // Skip row if date is empty
                        if (empty($value)) {
                            return null;
                        }
                        $data['transaction_date'] = $this->parseDate($value);
                        break;
                    case 'amount':
                        // Skip row if amount is empty
                        if (empty($value)) {
                            return null;
                        }
                        $data['amount'] = $this->parseAmount($value);
                        break;
                    case 'note':
                        $data['note'] = $value;
                        break;
                    case 'reference_number':
                        $data['reference_number'] = $value;
                        break;
                }
            }
        }

        // Validate required fields
        if (!isset($data['transaction_date']) || !isset($data['amount']) || !isset($data['type'])) {
            return null;
        }

        // Set default values if not provided
        if (!isset($data['note'])) {
            $data['note'] = '';
        }

        // Set needs_ai to false if not already set
        if (!isset($data['needs_ai'])) {
            $data['needs_ai'] = false;
        }

        return $data;
    }

    private function parseDate($value)
    {
        if (empty($value)) {
            return Carbon::now()->format('Y-m-d');
        }

        // Handle Excel date serial numbers
        if (is_numeric($value) && $value > 25569) {
            try {
                $unixDate = ($value - 25569) * 86400;
                return Carbon::createFromTimestamp($unixDate)->format('Y-m-d');
            } catch (\Exception $e) {
                // If Excel serial number parsing fails, continue with other methods
            }
        }

        // Convert to string and clean up
        $dateStr = trim(strval($value));
        
        // Common date formats to try in order of preference
        $dateFormats = [
            // ISO format (preferred)
            'Y-m-d',
            'Y-m-d H:i:s',
            'Y/m/d',
            'Y/m/d H:i:s',
            
            // DD/MM/YYYY formats
            'd/m/Y',
            'd/m/Y H:i:s',
            'd-m-Y',
            'd-m-Y H:i:s',
            'd.m.Y',
            'd.m.Y H:i:s',
            'd m Y',
            
            // MM/DD/YYYY formats  
            'm/d/Y',
            'm/d/Y H:i:s',
            'm-d-Y',
            'm-d-Y H:i:s',
            'm.d.Y',
            'm.d.Y H:i:s',
            'm d Y',
            
            // Two-digit year formats
            'd/m/y',
            'd/m/y H:i:s',
            'd-m-y',
            'd-m-y H:i:s',
            'd.m.y',
            'd.m.y H:i:s',
            'm/d/y',
            'm/d/y H:i:s',
            'm-d-y',
            'm-d-y H:i:s',
            'm.d.y',
            'm.d.y H:i:s',
            'y/m/d',
            'y-m-d',
            'y.m.d',
            
            // Text month formats
            'd M Y',
            'd F Y',
            'M d, Y',
            'F d, Y',
            'd M y',
            'd F y',
            'M d, y',
            'F d, y',
            'j M Y',
            'j F Y',
            'M j, Y',
            'F j, Y',
            'j M y',
            'j F y',
            'M j, y',
            'F j, y',
            
            // Other common formats
            'Y-m-d\TH:i:s',
            'Y-m-d\TH:i:s\Z',
            'Y-m-d\TH:i:s.u\Z',
            'D, d M Y H:i:s',
            'D, d M Y H:i:s O',
            'D, d M Y H:i:s T',
            'D M d Y H:i:s',
            'D M d Y',
            'l, F d, Y',
            'l, F d, Y H:i:s',
        ];

        // Try each format
        foreach ($dateFormats as $format) {
            try {
                $date = Carbon::createFromFormat($format, $dateStr);
                if ($date && $date->year >= 1900 && $date->year <= 2100) {
                    return $date->format('Y-m-d');
                }
            } catch (\Exception $e) {
                // Continue to next format
                continue;
            }
        }

        // Try Carbon's built-in parsing as a fallback
        try {
            $date = Carbon::parse($dateStr);
            if ($date && $date->year >= 1900 && $date->year <= 2100) {
                return $date->format('Y-m-d');
            }
        } catch (\Exception $e) {
            // Continue to manual parsing
        }

        // Try to manually parse common patterns
        $date = $this->parseManualDateFormats($dateStr);
        if ($date) {
            return $date;
        }

        // If all else fails, try to extract numbers and make assumptions
        $numbers = [];
        preg_match_all('/\d+/', $dateStr, $numbers);
        $numbers = $numbers[0];

        if (count($numbers) >= 3) {
            // Try different arrangements
            $arrangements = [
                [$numbers[2], $numbers[1], $numbers[0]], // DD/MM/YYYY -> YYYY-MM-DD
                [$numbers[2], $numbers[0], $numbers[1]], // MM/DD/YYYY -> YYYY-MM-DD
                [$numbers[0], $numbers[1], $numbers[2]], // YYYY-MM-DD
            ];

            foreach ($arrangements as $arr) {
                try {
                    $year = $arr[0];
                    $month = $arr[1];
                    $day = $arr[2];

                    // Handle two-digit years
                    if ($year < 100) {
                        $year += $year < 50 ? 2000 : 1900;
                    }

                    // Validate ranges
                    if ($year >= 1900 && $year <= 2100 && $month >= 1 && $month <= 12 && $day >= 1 && $day <= 31) {
                        $date = Carbon::create($year, $month, $day);
                        if ($date && $date->month == $month && $date->day == $day) {
                            return $date->format('Y-m-d');
                        }
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
        }

        // Last resort: return current date
        return Carbon::now()->format('Y-m-d');
    }

    private function parseManualDateFormats($dateStr)
    {
        // Handle formats like "15-Jan-2024", "Jan 15, 2024", "15 January 2024", etc.
        $monthNames = [
            'january' => 1, 'jan' => 1,
            'february' => 2, 'feb' => 2,
            'march' => 3, 'mar' => 3,
            'april' => 4, 'apr' => 4,
            'may' => 5,
            'june' => 6, 'jun' => 6,
            'july' => 7, 'jul' => 7,
            'august' => 8, 'aug' => 8,
            'september' => 9, 'sep' => 9, 'sept' => 9,
            'october' => 10, 'oct' => 10,
            'november' => 11, 'nov' => 11,
            'december' => 12, 'dec' => 12,
        ];

        $dateStr = strtolower($dateStr);
        
        foreach ($monthNames as $monthName => $monthNum) {
            if (strpos($dateStr, $monthName) !== false) {
                // Extract numbers from the string
                $numbers = [];
                preg_match_all('/\d+/', $dateStr, $numbers);
                $numbers = $numbers[0];

                if (count($numbers) >= 2) {
                    $day = null;
                    $year = null;

                    // Find day and year
                    foreach ($numbers as $num) {
                        if ($num >= 1 && $num <= 31 && !$day) {
                            $day = $num;
                        } elseif ($num >= 1900 && $num <= 2100) {
                            $year = $num;
                        } elseif ($num >= 0 && $num <= 99 && !$year) {
                            $year = $num + ($num < 50 ? 2000 : 1900);
                        }
                    }

                    if ($day && $year) {
                        try {
                            $date = Carbon::create($year, $monthNum, $day);
                            if ($date && $date->month == $monthNum && $date->day == $day) {
                                return $date->format('Y-m-d');
                            }
                        } catch (\Exception $e) {
                            continue;
                        }
                    }
                }
            }
        }

        return null;
    }

    private function parseAmount($value)
    {
        if (empty($value)) {
            return 0;
        }

        // Remove currency symbols, commas, and other non-numeric characters except decimal point and minus
        $amount = preg_replace('/[^\d.-]/', '', $value);

        // Convert to float and take absolute value (we handle +/- in type field)
        $numericAmount = floatval($amount);
        return abs($numericAmount);
    }

    private function parseType($value)
    {
        if (empty($value)) {
            return 'expense';
        }

        $value = strtolower(trim($value));

        // Check for explicit type values first
        if (in_array($value, ['income', 'credit', 'deposit', 'in', 'cr', '+', 'received', 'salary', 'bonus', 'refund'])) {
            return 'income';
        } elseif (in_array($value, ['expense', 'debit', 'withdrawal', 'out', 'dr', '-', 'payment', 'purchase', 'transfer', 'fee', 'charge'])) {
            return 'expense';
        }

        // Check for amount-based detection (if value contains amount with +/-)
        if (strpos($value, '+') !== false) {
            return 'income';
        } elseif (strpos($value, '-') !== false) {
            return 'expense';
        }

        return 'expense'; // default
    }

    private function findOrCreateCategoryId($categoryName, $userId)
    {
        if (empty($categoryName)) {
            return null;
        }

        $category = Category::where('user_id', $userId)->where('name', $categoryName)->first();

        if ($category) {
            return $category->id;
        } else {
            $newCategory = Category::create([
                'user_id' => $userId,
                'name' => $categoryName
            ]);
            return $newCategory->id;
        }
    }
}
