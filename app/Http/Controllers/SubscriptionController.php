<?php

namespace App\Http\Controllers;

use App\Services\PaddleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    protected $paddleService;

    public function __construct(PaddleService $paddleService)
    {
        $this->paddleService = $paddleService;
    }

    public function status(Request $request)
    {
        return response()->json(
            $this->paddleService->getSubscriptionStatus($request->user())
        );
    }

    public function create(Request $request)
    {
        $request->validate([
            'planId' => 'required|string'
        ]);

        $result = $this->paddleService->createSubscription(
            $request->user(),
            $request->input('planId')
        );

        if (!$result['success']) {
            return response()->json($result, 400);
        }

        return response()->json($result);
    }

    public function cancel(Request $request)
    {
        $result = $this->paddleService->cancelSubscription($request->user());
        
        if (!$result['success']) {
            return response()->json($result, 400);
        }

        return response()->json($result);
    }

    public function handleWebhook(Request $request)
    {
        $payload = $request->all();
        
        // Verify webhook signature if needed
        // Paddle provides a signature in the request headers
        
        try {
            $result = $this->paddleService->handleWebhook($payload);
            
            if (!$result['success']) {
                Log::error('Paddle webhook error', [
                    'error' => $result['error'] ?? 'Unknown error',
                    'payload' => $payload
                ]);
                return response()->json(['error' => 'Webhook handler failed'], 500);
            }

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('Paddle webhook error: ' . $e->getMessage(), [
                'payload' => $payload
            ]);
            return response()->json(['error' => 'Webhook handler failed'], 500);
        }
    }
} 