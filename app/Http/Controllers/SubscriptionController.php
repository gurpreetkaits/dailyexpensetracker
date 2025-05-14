<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\PolarService;

class SubscriptionController extends Controller
{
    protected $polarService;

    public function __construct(PolarService $polarService)
    {
        $this->polarService = $polarService;
    }

    public function createCheckoutSession(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        $planId = config('polar.plan_id');

        if (!$planId) {
            Log::error('Missing plan ID in checkout request', [
                'user_id' => $user->id,
                'request_data' => $request->all()
            ]);
            return response()->json(['error' => 'Plan ID is required'], 400);
        }

        try {
            $result = $this->polarService->createCheckoutSession($user, $planId);

            if (!$result['success']) {
                throw new \Exception($result['message']);
            }

            Log::info('Polar checkout session created successfully', [
                'user_id' => $user->id,
                'checkout_id' => $result['checkout_id']
            ]);

            return response()->json([
                'checkout_id' => $result['checkout_id'],
                'checkout_url' => $result['checkout_url']
            ]);
        } catch (\Exception $e) {
            Log::error('Unexpected error in checkout session creation', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Unable to create checkout session: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getSubscriptionStatus(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'hasActiveSubscription' => $user->subscription_status === 'active',
            'subscription' => [
                'status' => $user->subscription_status,
                'plan' => $user->subscription_plan,
                'ends_at' => $user->subscription_ends_at
            ]
        ]);
    }

    public function verifyCheckoutSession(Request $request)
    {
        $user = $request->user();
        $checkoutId = $request->get('checkout_id');

        try {
            $result = $this->polarService->verifyCheckoutSession($checkoutId);

            if (!$result['success']) {
                throw new \Exception($result['message']);
            }

            $user->update([
                'polar_id' => $result['subscription_id'],
                'subscription_status' => $result['status'],
                'subscription_plan' => $result['plan_id']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Subscription activated successfully!',
                'subscription' => [
                    'status' => $result['status'],
                    'plan_id' => $result['plan_id']
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Subscription verification failed', [
                'user_id' => $user->id,
                'checkout_id' => $checkoutId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to verify subscription: ' . $e->getMessage()
            ], 400);
        }
    }

    public function handleSubscriptionSuccess(Request $request)
    {
        return redirect()->route('overview')->with('success', 'Subscription activated successfully!');
    }

    public function cancel()
    {
        return redirect()->route('pricing')->with('info', 'Subscription checkout was cancelled.');
    }

    public function cancelSubscription(Request $request)
    {
        $user = $request->user();

        try {
            $user->update([
                'subscription_status' => 'cancelled',
                'subscription_ends_at' => now()
            ]);
            return response()->json(['message' => 'Subscription cancelled successfully']);
        } catch (\Exception $e) {
            Log::error('Error cancelling subscription', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Unable to cancel subscription'], 500);
        }
    }

    public function resumeSubscription(Request $request)
    {
        $user = $request->user();

        try {
            $user->update([
                'subscription_status' => 'active',
                'subscription_ends_at' => null
            ]);
            return response()->json(['message' => 'Subscription resumed successfully']);
        } catch (\Exception $e) {
            Log::error('Error resuming subscription', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Unable to resume subscription'], 500);
        }
    }

    public function history(Request $request)
    {
        $user = $request->user();
        $perPage = $request->get('per_page', 10);

        $subscriptions = $user->subscriptions()
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json($subscriptions);
    }
}
