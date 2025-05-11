<?php

namespace App\Http\Controllers;

use App\Services\ChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatController extends Controller
{
    public function __construct(private ChatService $chatService)
    {
    }
    public function store(Request $request)
    {
        $response = $this->chatService->sendMessage($request->message);
        return response()->json($response);
        
    }
}
