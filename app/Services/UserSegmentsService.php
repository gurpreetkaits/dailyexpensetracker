<?php

namespace App\Services;

// app/Services/UserSegments.php
use App\Models\User;

class UserSegmentsService
{
    /** Users who made NO transaction in the last N days (default 7) */
    public static function inactive(int $days = 30)
    {
        return User::whereDoesntHave('transactions', function ($q) use ($days) {
            $q->where('created_at', '>=', now()->subDays($days));
        })
        ->get();
    }

    public static function active()
    {
        return User::active()->get();
    }
}
