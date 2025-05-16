<?php

namespace App\Http\Controllers;

use App\Services\PolarService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PolarSubscriptionController extends Controller
{
    private PolarService $polarService;
    public function __construct()
    {
        $this->polarService = app(PolarService::class);
    }

    public function index(Request $request): JsonResponse
    {
        abort_if(!$request->user(), 401, 'Unauthorized');
        $subscriptions = $request->user()->subscriptions->paginate(10);

        return response()->json($subscriptions);
    }

    public function handleWebhook(Request $request)
    {
        $payload = $request->all();
        $signature = $request->header('X-Polar-Signature');

        if (!$signature) {
            return response()->json(['error' => 'Signature header missing'], 400);
        }

        $secret = config('services.polar.webhook_secret');

        if (!$secret) {
            return response()->json(['error' => 'Webhook secret not configured'], 500);
        }

        $payload = json_encode($payload);

        $expectedSignature = hash_hmac('sha256', $payload, $secret);

        if ($expectedSignature !== $signature) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        dd($payload);
    }

    public function getSubscriptionStatus(Request $request): JsonResponse
    {
        $user = $request->user();
        $subscription = $user->activeSubscription()->first();
        
        return response()->json([
            'subscription' => $subscription,
            'user' => $user
        ]);
    }

    public function verifyCheckoutSession(Request $request): JsonResponse
    {
        abort_if(!$request->user(), 401, 'Unauthorized');
        $user = $request->user();
        $checkoutSessionId = $request->input('checkout_id');
        
        if(!$checkoutSessionId) {
            return response()->json(['error' => 'Checkout session ID is required'], 400);
        }

        $result = $this->polarService->processCheckoutSession($checkoutSessionId, $user);

        if (!$result['success']) {
            return response()->json([
                'error' => $result['error'],
                'message' => $result['message'] ?? null,
                'status' => $result['status'] ?? null
            ], 400);
        }

        return response()->json($result);
    }
}
