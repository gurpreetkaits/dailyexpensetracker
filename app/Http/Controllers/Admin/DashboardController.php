<?php
// app/Http/Controllers/Admin/DashboardController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __invoke(Request $request): \Illuminate\Http\JsonResponse
    {

        abort_if(!Auth::user()->is_admin,403);
        // ── numbers ──────────────────────────────────────────────
        $totalUsers       = User::count();
        $activeUsersCount = Transaction::where('created_at', '>=', now()->subDays(2))
            ->distinct('user_id')
            ->count('user_id');;
        $totalTx          = Transaction::count();
        $txLastHour       = Transaction::where('created_at', '>=', now()->subHour())->count();

        // ── lists (max 10) ───────────────────────────────────────
        $recentUsers = User::latest()
            ->select('id', 'name', 'email', 'avatar', 'created_at')
            ->take(10)
            ->get()
            ->map(fn ($u) => [
                'id'     => $u->id,
                'name'   => $u->name,
                'email'  => $u->email,
                'avatar' => $u->avatar ?: "https://i.pravatar.cc/40?u=$u->id",
                'joined' => $u->created_at->toIso8601String(),
            ]);

        $recentTx = Transaction::with('user:id,name')
            ->latest()
            ->take(10)
            ->get()
            ->map(fn ($t) => [
                'id'     => $t->id,
                'type' => $t->type,
                'user'   => $t->user->name ?? '—',
                'amount' => $t->amount,
                'date'   => $t->created_at->toIso8601String(),
            ]);

        // ── payload exactly what Vue expects ─────────────────────
        return response()->json([
            'stats' => [
                'totalUsers'       => $totalUsers,
                'activeUsers'      => $activeUsersCount,
                'totalTransactions'=> $totalTx,
                'txLastHour'       => $txLastHour,
            ],
            'users'        => $recentUsers,
            'transactions' => $recentTx,
        ]);
    }
}
