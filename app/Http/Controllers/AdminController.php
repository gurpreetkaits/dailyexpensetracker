<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// app/Http/Controllers/AdminController.php
class AdminController extends Controller
{

    public function index()
    {
        return view('login');
    }

    public function store(Request $request){
        dd($request->all());
    }

    public function dashboard()
    {
        $stats = [
            'totalUsers' => User::count(),
            'totalTransactions' => Transaction::count(),
            'totalAmount' => Transaction::sum('amount')
        ];

        $users = User::withCount('transactions')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('father', compact('stats', 'users'));
    }
}