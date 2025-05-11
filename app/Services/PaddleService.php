<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaddleService
{
    protected $vendorId;
    protected $apiKey;
    protected $environment;
    protected $baseUrl;

    public function __construct()
    {
        $this->vendorId = config('paddle.vendor_id');
        $this->apiKey = config('paddle.api_key');
        $this->environment = config('paddle.environment', 'sandbox');
        $this->baseUrl = $this->environment === 'sandbox' 
            ? 'https://sandbox-vendors.paddle.com/api/2.0'
            : 'https://vendors.paddle.com/api/2.0';
    }

    public function createSubscription(User $user, string $planId)
    {
        try {
            $response = Http::post($this->baseUrl . '/subscription/create', [
                'vendor_id' => $this->vendorId,
                'vendor_auth_code' => $this->apiKey,
                'plan_id' => $planId,
                'customer_email' => $user->email,
                'customer_country' => $user->country ?? 'US',
                'return_url' => config('app.url') . '/subscription/success',
                'webhook_url' => config('app.url') . '/api/paddle/webhook'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if ($data['success']) {
                    $user->update([
                        'paddle_subscription_id' => $data['subscription_id'],
                        'paddle_customer_id' => $data['customer_id']
                    ]);

                    return [
                        'success' => true,
                        'checkout_url' => $data['checkout_url'],
                        'message' => 'Subscription checkout created successfully'
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'Failed to create subscription checkout'
            ];
        } catch (\Exception $e) {
            Log::error('Paddle subscription creation error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while creating subscription'
            ];
        }
    }

    public function cancelSubscription(User $user)
    {
        try {
            if (!$user->paddle_subscription_id) {
                return [
                    'success' => false,
                    'message' => 'No active subscription found'
                ];
            }

            $response = Http::post($this->baseUrl . '/subscription/cancel', [
                'vendor_id' => $this->vendorId,
                'vendor_auth_code' => $this->apiKey,
                'subscription_id' => $user->paddle_subscription_id
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if ($data['success']) {
                    $user->update([
                        'paddle_subscription_id' => null,
                        'subscription_status' => 'cancelled'
                    ]);

                    return [
                        'success' => true,
                        'message' => 'Subscription cancelled successfully'
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'Failed to cancel subscription'
            ];
        } catch (\Exception $e) {
            Log::error('Paddle subscription cancellation error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while cancelling subscription'
            ];
        }
    }

    public function getSubscriptionStatus(User $user)
    {
        if (!$user->paddle_subscription_id) {
            return [
                'status' => 'none',
                'plan' => null,
                'ends_at' => null
            ];
        }

        try {
            $response = Http::post($this->baseUrl . '/subscription/users', [
                'vendor_id' => $this->vendorId,
                'vendor_auth_code' => $this->apiKey,
                'subscription_id' => $user->paddle_subscription_id
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if ($data['success']) {
                    $subscription = $data['subscription'];
                    return [
                        'status' => $subscription['state'],
                        'plan' => $subscription['plan_id'],
                        'ends_at' => $subscription['next_bill_date'],
                        'cancelled' => $subscription['state'] === 'cancelled',
                        'on_trial' => $subscription['trial_end'] ? true : false,
                        'trial_ends_at' => $subscription['trial_end']
                    ];
                }
            }

            return [
                'status' => 'unknown',
                'plan' => null,
                'ends_at' => null
            ];
        } catch (\Exception $e) {
            Log::error('Paddle subscription status error: ' . $e->getMessage());
            return [
                'status' => 'error',
                'plan' => null,
                'ends_at' => null
            ];
        }
    }

    public function handleWebhook(array $payload)
    {
        try {
            $eventType = $payload['alert_name'] ?? null;
            
            switch ($eventType) {
                case 'subscription_created':
                case 'subscription_updated':
                    $this->handleSubscriptionUpdate($payload);
                    break;
                    
                case 'subscription_cancelled':
                    $this->handleSubscriptionCancellation($payload);
                    break;
                    
                case 'subscription_payment_succeeded':
                    $this->handlePaymentSuccess($payload);
                    break;
                    
                case 'subscription_payment_failed':
                    $this->handlePaymentFailure($payload);
                    break;
            }

            return ['success' => true];
        } catch (\Exception $e) {
            Log::error('Paddle webhook error: ' . $e->getMessage(), [
                'event' => $eventType,
                'payload' => $payload
            ]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    protected function handleSubscriptionUpdate(array $payload)
    {
        $user = \App\Models\User::where('paddle_subscription_id', $payload['subscription_id'])->first();
        if ($user) {
            $user->update([
                'subscription_status' => $payload['status'],
                'paddle_customer_id' => $payload['customer_id']
            ]);
        }
    }

    protected function handleSubscriptionCancellation(array $payload)
    {
        $user = \App\Models\User::where('paddle_subscription_id', $payload['subscription_id'])->first();
        if ($user) {
            $user->update([
                'subscription_status' => 'cancelled',
                'paddle_subscription_id' => null
            ]);
        }
    }

    protected function handlePaymentSuccess(array $payload)
    {
        $user = \App\Models\User::where('paddle_subscription_id', $payload['subscription_id'])->first();
        if ($user) {
            $user->update([
                'subscription_status' => 'active',
                'last_payment_at' => now()
            ]);
        }
    }

    protected function handlePaymentFailure(array $payload)
    {
        $user = \App\Models\User::where('paddle_subscription_id', $payload['subscription_id'])->first();
        if ($user) {
            $user->update([
                'subscription_status' => 'payment_failed',
                'last_payment_failed_at' => now()
            ]);
        }
    }
} 