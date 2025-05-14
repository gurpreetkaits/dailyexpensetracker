<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PolarService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('polar.api_key');
        $this->baseUrl = config('polar.base_url');
    }

    public function createCheckoutSession(User $user, string $planId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->post($this->baseUrl . '/checkout/create', [
                'plan_id' => $planId,
                'user_email' => $user->email,
                'success_url' => config('app.url') . '/plans?checkout_id={CHECKOUT}',
                'cancel_url' => config('app.url') . '/plans'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'checkout_url' => $data['checkout_url'],
                    'checkout_id' => $data['checkout_id']
                ];
            }

            Log::error('Failed to create Polar checkout session', [
                'user_id' => $user->id,
                'plan_id' => $planId,
                'response' => $response->json()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create checkout session'
            ];
        } catch (\Exception $e) {
            Log::error('Error creating Polar checkout session', [
                'user_id' => $user->id,
                'plan_id' => $planId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'An error occurred while creating checkout session'
            ];
        }
    }

    public function verifyCheckoutSession(string $checkoutId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->get($this->baseUrl . '/checkout/' . $checkoutId);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'subscription_id' => $data['subscription_id'],
                    'status' => $data['status'],
                    'plan_id' => $data['plan_id']
                ];
            }

            Log::error('Failed to verify Polar checkout session', [
                'checkout_id' => $checkoutId,
                'response' => $response->json()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to verify checkout session'
            ];
        } catch (\Exception $e) {
            Log::error('Error verifying Polar checkout session', [
                'checkout_id' => $checkoutId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'An error occurred while verifying checkout session'
            ];
        }
    }

    public function handleWebhook(array $payload)
    {
        $eventType = $payload['type'] ?? null;
        
        try {
            switch ($eventType) {
                case 'subscription.created':
                case 'subscription.updated':
                    return $this->handleSubscriptionUpdate($payload);
                    
                case 'subscription.cancelled':
                    return $this->handleSubscriptionCancellation($payload);
                    
                case 'subscription.payment.succeeded':
                    return $this->handlePaymentSuccess($payload);
                    
                case 'subscription.payment.failed':
                    return $this->handlePaymentFailure($payload);
            }

            return ['success' => true];
        } catch (\Exception $e) {
            Log::error('Polar webhook error', [
                'event' => $eventType,
                'payload' => $payload,
                'error' => $e->getMessage()
            ]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    protected function handleSubscriptionUpdate(array $payload)
    {
        $user = User::where('polar_id', $payload['user_id'])->first();
        if ($user) {
            $user->update([
                'subscription_status' => $payload['status'],
                'subscription_plan' => $payload['plan_id']
            ]);
        }
        return ['success' => true];
    }

    protected function handleSubscriptionCancellation(array $payload)
    {
        $user = User::where('polar_id', $payload['user_id'])->first();
        if ($user) {
            $user->update([
                'subscription_status' => 'cancelled',
                'subscription_plan' => null
            ]);
        }
        return ['success' => true];
    }

    protected function handlePaymentSuccess(array $payload)
    {
        $user = User::where('polar_id', $payload['user_id'])->first();
        if ($user) {
            $user->update([
                'subscription_status' => 'active'
            ]);
        }
        return ['success' => true];
    }

    protected function handlePaymentFailure(array $payload)
    {
        $user = User::where('polar_id', $payload['user_id'])->first();
        if ($user) {
            $user->update([
                'subscription_status' => 'payment_failed'
            ]);
        }
        return ['success' => true];
    }
} 