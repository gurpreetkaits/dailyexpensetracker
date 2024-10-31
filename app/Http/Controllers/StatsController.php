<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    public function index()
    {
        if (auth()->user()?->email !== 'gurpreetkait.codes@gmail.com') {
            abort(403, 'unauthorized');
        }
        $stats = [
            'totalUsers' => User::count(),
            'totalTransactions' => Transaction::count(),
            'totalAmount' => Transaction::sum('amount')
        ];

        $users = User::withCount('transactions')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([$stats, $users], 200);
    }
}
