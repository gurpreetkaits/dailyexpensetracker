<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\RecurringExpense;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RecurringExpenseDetectionService
{
    /**
     * Detect potential recurring expenses from transaction history
     */
    public function detectRecurringPatterns(int $userId): array
    {
        // Get transactions from the last 6 months
        $transactions = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->where('transaction_date', '>=', Carbon::now()->subMonths(6))
            ->orderBy('transaction_date', 'desc')
            ->get();

        if ($transactions->count() < 2) {
            return [];
        }

        $suggestions = [];

        // Group transactions by similar amounts (within 5% tolerance)
        $amountGroups = $this->groupByAmount($transactions);

        foreach ($amountGroups as $group) {
            if ($group->count() >= 2) {
                $pattern = $this->analyzePattern($group);
                if ($pattern) {
                    // Check if this pattern already exists as recurring expense
                    $exists = RecurringExpense::where('user_id', $userId)
                        ->where('amount', $pattern['amount'])
                        ->where('is_active', true)
                        ->exists();

                    if (!$exists) {
                        $suggestions[] = $pattern;
                    }
                }
            }
        }

        // Also check by note/description similarity
        $noteGroups = $this->groupBySimilarNotes($transactions);
        foreach ($noteGroups as $group) {
            if ($group->count() >= 2) {
                $pattern = $this->analyzePattern($group);
                if ($pattern && !$this->isDuplicate($suggestions, $pattern)) {
                    $exists = RecurringExpense::where('user_id', $userId)
                        ->where('name', 'like', '%' . $pattern['name'] . '%')
                        ->where('is_active', true)
                        ->exists();

                    if (!$exists) {
                        $suggestions[] = $pattern;
                    }
                }
            }
        }

        // Sort by confidence score
        usort($suggestions, function ($a, $b) {
            return $b['confidence'] <=> $a['confidence'];
        });

        return array_slice($suggestions, 0, 10); // Return top 10 suggestions
    }

    /**
     * Group transactions by similar amounts
     */
    private function groupByAmount(Collection $transactions): array
    {
        $groups = [];
        $processed = [];

        foreach ($transactions as $transaction) {
            if (in_array($transaction->id, $processed)) {
                continue;
            }

            $amount = (float) $transaction->amount;
            $tolerance = $amount * 0.05; // 5% tolerance

            $similar = $transactions->filter(function ($t) use ($amount, $tolerance, $processed) {
                if (in_array($t->id, $processed)) {
                    return false;
                }
                $diff = abs((float) $t->amount - $amount);
                return $diff <= $tolerance;
            });

            if ($similar->count() >= 2) {
                $groups[] = $similar;
                foreach ($similar as $t) {
                    $processed[] = $t->id;
                }
            }
        }

        return $groups;
    }

    /**
     * Group transactions by similar notes
     */
    private function groupBySimilarNotes(Collection $transactions): array
    {
        $groups = [];

        // Group by exact note match first
        $byNote = $transactions->groupBy(function ($t) {
            return strtolower(trim($t->note ?? ''));
        });

        foreach ($byNote as $note => $group) {
            if (!empty($note) && $group->count() >= 2) {
                $groups[] = $group;
            }
        }

        // Group by category
        $byCategory = $transactions->groupBy('category_id');
        foreach ($byCategory as $categoryId => $group) {
            if ($categoryId && $group->count() >= 2) {
                // Further filter by similar amounts within category
                $amountGroups = $this->groupByAmount($group);
                foreach ($amountGroups as $amountGroup) {
                    if ($amountGroup->count() >= 2) {
                        $groups[] = $amountGroup;
                    }
                }
            }
        }

        return $groups;
    }

    /**
     * Analyze a group of transactions to detect recurring pattern
     */
    private function analyzePattern(Collection $group): ?array
    {
        if ($group->count() < 2) {
            return null;
        }

        $sorted = $group->sortBy('transaction_date')->values();
        $intervals = [];

        for ($i = 1; $i < $sorted->count(); $i++) {
            $prev = Carbon::parse($sorted[$i - 1]->transaction_date);
            $curr = Carbon::parse($sorted[$i]->transaction_date);
            $intervals[] = $prev->diffInDays($curr);
        }

        if (empty($intervals)) {
            return null;
        }

        $avgInterval = array_sum($intervals) / count($intervals);
        $recurrence = $this->detectRecurrence($avgInterval);

        if (!$recurrence) {
            return null;
        }

        // Calculate confidence based on consistency
        $variance = $this->calculateVariance($intervals, $avgInterval);
        $confidence = $this->calculateConfidence($group->count(), $variance, $avgInterval);

        if ($confidence < 50) {
            return null;
        }

        $avgAmount = $group->avg('amount');
        $firstTransaction = $sorted->first();
        $mostRecentTransaction = $sorted->last();

        // Determine name from note or category
        $name = $this->determineName($group);

        // Get the payment day from most recent transaction
        $paymentDay = Carbon::parse($mostRecentTransaction->transaction_date)->day;

        return [
            'name' => $name,
            'amount' => round($avgAmount, 2),
            'recurrence' => $recurrence,
            'payment_day' => $paymentDay,
            'type' => $this->guessType($name, $avgAmount),
            'confidence' => round($confidence),
            'occurrences' => $group->count(),
            'first_payment_date' => Carbon::parse($firstTransaction->transaction_date)->format('Y-m-d'),
            'category_id' => $firstTransaction->category_id,
            'wallet_id' => $firstTransaction->wallet_id,
            'sample_transactions' => $group->take(3)->map(function ($t) {
                return [
                    'date' => $t->transaction_date,
                    'amount' => $t->amount,
                    'note' => $t->note
                ];
            })->values()->toArray()
        ];
    }

    /**
     * Detect recurrence type from average interval
     */
    private function detectRecurrence(float $avgInterval): ?string
    {
        // Monthly: 28-31 days
        if ($avgInterval >= 25 && $avgInterval <= 35) {
            return 'monthly';
        }

        // Quarterly: ~90 days
        if ($avgInterval >= 85 && $avgInterval <= 95) {
            return 'quarterly';
        }

        // Yearly: ~365 days
        if ($avgInterval >= 355 && $avgInterval <= 375) {
            return 'yearly';
        }

        // Weekly: ~7 days (but we'll convert to monthly for simplicity)
        if ($avgInterval >= 5 && $avgInterval <= 9) {
            return 'monthly'; // Will show as ~4x per month
        }

        return null;
    }

    /**
     * Calculate variance of intervals
     */
    private function calculateVariance(array $intervals, float $mean): float
    {
        if (count($intervals) < 2) {
            return 0;
        }

        $squaredDiffs = array_map(function ($interval) use ($mean) {
            return pow($interval - $mean, 2);
        }, $intervals);

        return sqrt(array_sum($squaredDiffs) / count($squaredDiffs));
    }

    /**
     * Calculate confidence score (0-100)
     */
    private function calculateConfidence(int $occurrences, float $variance, float $avgInterval): float
    {
        $score = 50; // Base score

        // More occurrences = higher confidence
        $score += min($occurrences * 5, 25);

        // Lower variance = higher confidence
        $normalizedVariance = $variance / max($avgInterval, 1);
        $score += max(0, 25 - ($normalizedVariance * 50));

        return min(100, $score);
    }

    /**
     * Determine name from transaction group
     */
    private function determineName(Collection $group): string
    {
        // Try to get most common note
        $notes = $group->pluck('note')->filter()->countBy();
        if ($notes->isNotEmpty()) {
            $mostCommon = $notes->sortDesc()->keys()->first();
            if (!empty($mostCommon)) {
                return ucfirst(strtolower($mostCommon));
            }
        }

        // Fall back to category name
        $firstWithCategory = $group->first(function ($t) {
            return $t->category;
        });

        if ($firstWithCategory && $firstWithCategory->category) {
            return ucfirst($firstWithCategory->category->name);
        }

        return 'Recurring Expense';
    }

    /**
     * Guess the type of recurring expense
     */
    private function guessType(string $name, float $amount): string
    {
        $nameLower = strtolower($name);

        // Subscription keywords
        $subscriptionKeywords = ['netflix', 'spotify', 'amazon prime', 'youtube', 'disney',
            'hulu', 'apple', 'icloud', 'google', 'microsoft', 'adobe', 'dropbox',
            'gym', 'membership', 'subscription', 'premium'];

        foreach ($subscriptionKeywords as $keyword) {
            if (str_contains($nameLower, $keyword)) {
                return 'subscription';
            }
        }

        // Bill keywords
        $billKeywords = ['electricity', 'electric', 'water', 'gas', 'internet', 'phone',
            'mobile', 'broadband', 'wifi', 'rent', 'insurance', 'utility', 'bill'];

        foreach ($billKeywords as $keyword) {
            if (str_contains($nameLower, $keyword)) {
                return 'bill';
            }
        }

        // EMI/Loan keywords
        $emiKeywords = ['emi', 'loan', 'mortgage', 'car loan', 'home loan', 'personal loan'];

        foreach ($emiKeywords as $keyword) {
            if (str_contains($nameLower, $keyword)) {
                return 'emi';
            }
        }

        // Default to other
        return 'other';
    }

    /**
     * Check if a pattern is duplicate
     */
    private function isDuplicate(array $suggestions, array $pattern): bool
    {
        foreach ($suggestions as $existing) {
            // Same amount within 10%
            $amountDiff = abs($existing['amount'] - $pattern['amount']);
            if ($amountDiff / max($existing['amount'], 1) < 0.1) {
                return true;
            }

            // Same name
            if (strtolower($existing['name']) === strtolower($pattern['name'])) {
                return true;
            }
        }

        return false;
    }
}
