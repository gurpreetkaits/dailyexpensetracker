<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function store(Request $request)
    {
        $feedback = $request->validate([
            'feedback' => 'required|string',
        ]);

    }
}
