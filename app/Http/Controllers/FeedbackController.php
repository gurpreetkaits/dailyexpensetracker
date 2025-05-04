<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function store(Request $request)
    {
        $validate = $request->validate([
            'feedback' => 'required|string',
        ]);

        Feedback::create([
            'user_id' => $request->user()->id,
            'feedback' => $validate['feedback'],
        ]);

        return response()->json([
            'message' => 'Feedback submitted successfully',
            'feedback' => $validate['feedback'],
        ], 201);
    }
}
