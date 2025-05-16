<?php

namespace App\Http\Controllers;

use App\Services\ChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class ChatController extends Controller
{
    private const MAX_MESSAGE_LENGTH = 1000;
    private const RATE_LIMIT_ATTEMPTS = 10;
    private const RATE_LIMIT_DECAY_MINUTES = 1;

    public function __construct(
        private ChatService $chatService
    ) {
        // Apply middleware in routes file instead
    }

    public function store(Request $request)
    {
        try {
            // Validate input
            $validated = $request->validate([
                'message' => ['required', 'string', 'max:' . self::MAX_MESSAGE_LENGTH, 'min:1']
            ]);

            $user = $request->user();
            
            // Check subscription with proper error handling
            $subscription = $user->activeSubscription()->first();
            
            if (!$subscription) {
                return response()->json([
                    'error' => 'No active subscription found',
                    'code' => 'SUBSCRIPTION_NOT_FOUND'
                ], 403);
            }

            if ($subscription->ends_at && $subscription->ends_at < now()) {
                return response()->json([
                    'error' => 'Subscription has expired',
                    'code' => 'SUBSCRIPTION_EXPIRED',
                    'expired_at' => $subscription->ends_at
                ], 403);
            }

            // Sanitize message
            $sanitizedMessage = strip_tags($validated['message']);
            
            // Log the request for audit
            Log::info('Chat message sent', [
                'user_id' => $user->id,
                'message_length' => strlen($sanitizedMessage),
                'subscription_id' => $subscription->id
            ]);

            // Send message with error handling
            try {
                $response = $this->chatService->sendMessage($sanitizedMessage);
                return response()->json($response);
            } catch (\Exception $e) {
                Log::error('Chat service error', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
                
                return response()->json([
                    'error' => 'Failed to process message',
                    'code' => 'CHAT_SERVICE_ERROR'
                ], 500);
            }

        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Invalid message format',
                'code' => 'VALIDATION_ERROR',
                'details' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Unexpected error in chat controller', [
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'error' => 'An unexpected error occurred',
                'code' => 'INTERNAL_ERROR'
            ], 500);
        }
    }
}
