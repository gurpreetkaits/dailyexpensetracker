<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Models\User;
use App\Notifications\EmailRecievedFeedbackNotificaiton;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class FeedbackController extends Controller
{
    public function index(){

        abort_if(!Auth::user()->is_admin,403);
        $feedbacks = Feedback::with('user')->latest()->paginate(10);
        return response()->json($feedbacks, 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validate = $request->validate([
            'feedback' => 'required|string',
        ]);

        Notification::send($request->user(), new EmailRecievedFeedbackNotificaiton($validate['feedback']));

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
