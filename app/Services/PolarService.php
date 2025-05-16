<?php

namespace App\Services;

use App\Http\Integrations\Polar\PolarConnector;
use App\Models\PolarSubscription;
use App\Models\PolarSubscriptionItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use JsonException;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;

class PolarService
{
    public function __construct(private PolarConnector $connector)
    {
    }

    /**
     * @throws FatalRequestException
     * @throws RequestException
     * @throws JsonException
     */
    public function getCheckoutSessionById(string $id)
    {
        return $this->connector->getCheckoutSession($id);
    }

    /**
     * Process a checkout session and create/update subscription
     *
     * @param string $checkoutSessionId
     * @param User $user
     * @return array{success: bool, error?: string, message?: string, status?: string, subscription?: mixed}
     * @throws FatalRequestException
     * @throws RequestException
     * @throws JsonException
     */
    public function processCheckoutSession(string $checkoutSessionId, User $user): array
    {
        try {
            $checkoutSession = $this->getCheckoutSessionById($checkoutSessionId);

            if (!isset($checkoutSession['status'])) {
                return [
                    'success' => false,
                    'error' => 'Invalid checkout session response',
                    'message' => 'Checkout session status is missing'
                ];
            }

            if ($checkoutSession['status'] !== 'succeeded') {
                return [
                    'success' => false,
                    'error' => 'Payment not completed',
                    'status' => $checkoutSession['status']
                ];
            }

            $subscription = null;
            $subscriptionResponse = [];

            if (isset($checkoutSession['customer_id'])) {
                try {
                    $subscription = $this->getSubscriptionDetails($checkoutSession['customer_id']);
                } catch (\Exception $e) {
                    Log::warning('Failed to fetch subscription details', [
                        'customer_id' => $checkoutSession['customer_id'],
                        'error' => $e->getMessage()
                    ]);
                }
            }

            return DB::transaction(function () use ($checkoutSession, $user, $subscription, $subscriptionResponse) {
                $subscriptionData = [
                    'user_id' => $user->id,
                    'name' => $checkoutSession['product']['name'] ?? 'Pro Plan',
                    'polar_id' => $checkoutSession['id'] ?? null,
                    'plan_id' => $checkoutSession['product_price']['id'] ?? null,
                    'status' => 'active',
                    'quantity' => 1,
                    'trial_ends_at' => null,
                    'ends_at' => $subscription->ends_at ? Carbon::parse($subscription->ends_at)->format('Y-m-d H:i:s') : null,
                    'canceled_at' => $subscription->canceled_at ? Carbon::parse($subscription->canceled_at)->format('Y-m-d H:i:s') : null,
                    'response' => json_encode($checkoutSession, JSON_THROW_ON_ERROR)
                ];

                $dbSubscription = PolarSubscription::updateOrCreate(
                    ['user_id' => $user->id],
                    $subscriptionData
                );


                if (isset($checkoutSession['product_price'], $checkoutSession['product']['id'])) {
                    $itemData = [
                        'subscription_id' => $dbSubscription->id,
                        'polar_id' => $checkoutSession['id'] ?? null,
                        'product_id' => $checkoutSession['product']['id'],
                        'plan_id' => $checkoutSession['product_price']['id'],
                        'quantity' => 1
                    ];

                    PolarSubscriptionItem::updateOrCreate(
                        [
                            'subscription_id' => $dbSubscription->id,
                            'product_id' => $checkoutSession['product']['id']
                        ],
                        $itemData
                    );
                }

                return [
                    'success' => true,
                    'message' => 'Subscription activated successfully',
                    'subscription' => $dbSubscription
                ];
            });

        } catch (JsonException $e) {
            Log::error('JSON encoding error in checkout session', [
                'error' => $e->getMessage(),
                'checkout_id' => $checkoutSessionId
            ]);
            return [
                'success' => false,
                'error' => 'Failed to process subscription data',
                'message' => 'Invalid subscription data format'
            ];
        } catch (\Exception $e) {
            Log::error('Error processing checkout session', [
                'error' => $e->getMessage(),
                'checkout_id' => $checkoutSessionId,
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'success' => false,
                'error' => 'Failed to process subscription',
                'message' => 'An unexpected error occurred'
            ];
        }
    }

    /**
     * @throws FatalRequestException
     * @throws RequestException
     * @throws JsonException
     */
    public function getSubscriptionDetails(string $subscription_id): \App\Data\PolarCustomerStateResponseData
    {
        $subscription = $this->connector->getSubscription($subscription_id);

        return $subscription;
    }
}
