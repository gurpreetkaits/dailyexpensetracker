<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Cashier\Exceptions\IncompletePayment;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Price;

class SubscriptionController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('cashier.secret'));
    }

    public function createCheckoutSession(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        $priceId = config('stripe.production_pricing_id');

        if (!$priceId) {
            Log::error('Missing price ID in checkout request', [
                'user_id' => $user->id,
                'request_data' => $request->all()
            ]);
            return response()->json(['error' => 'Price ID is required'], 400);
        }

        try {
            $frontendUrl = env('FRONTEND_URL');
            $returnUrl = $request->header('Referer') ?? $frontendUrl;

            // Ensure we have a clean URL without any existing query parameters
            $returnUrl = strtok($returnUrl, '?');


            $checkout = $user->newSubscription('pro', $priceId)
                ->checkout([
                    'success_url' => $returnUrl . 'plans?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url' => $returnUrl,
                    'billing_address_collection' => 'required',
                    'metadata' => [
                        'user_id' => $user->id,
                        'price_id' => $priceId,
                        'return_url' => $returnUrl
                    ]
                ]);

            Log::info('Stripe checkout session created successfully', [
                'user_id' => $user->id,
                'session_id' => $checkout->id,
                'price_id' => $priceId,
                'return_url' => $returnUrl
            ]);

            return response()->json([
                'session_id' => $checkout->id
            ]);
        } catch (IncompletePayment $e) {
            Log::error('Incomplete payment', [
                'user_id' => $user->id,
                'payment_id' => $e->payment->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Payment requires additional confirmation.',
                'payment_id' => $e->payment->id
            ], 402);
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
            'hasActiveSubscription' => $user->subscribed('pro'),
            'subscription' => $user->subscription('pro'),
            'defaultPaymentMethod' => $user->defaultPaymentMethod()
        ]);
    }

    public function verifyCheckoutSession(Request $request){
        $user = $request->user();

        try {
            $session = $user->stripe()->checkout->sessions->retrieve($request->get('session_id'));

            if ($session->payment_status === 'paid') {
                // Get the subscription from the session
                $subscription = $user->subscriptions()->where('stripe_id', $session->subscription)->first();

                if (!$subscription) {
                    // If subscription doesn't exist, create it
                    $subscription = $user->subscriptions()->create([
                        'type' => 'pro',
                        'stripe_id' => $session->subscription,
                        'stripe_status' => $session->subscription_status ?? 'active',
                        'stripe_price' => $session->metadata->price_id ?? null,
                        'quantity' => 1,
                        'trial_ends_at' => null,
                        'ends_at' => now()->addDays(30),
                    ]);

                    // Create subscription items
                    $subscription->items()->create([
                        'stripe_id' => $session->subscription,
                        'stripe_product' => $session->metadata->product_id ?? 'pro',
                        'stripe_price' => $session->metadata->price_id ?? null,
                        'quantity' => 1,
                    ]);
                }

                // Update subscription status if needed
                if ($subscription->stripe_status !== $session->subscription_status) {
                    $subscription->update([
                        'stripe_status' => $session->subscription_status ?? 'active'
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Subscription activated successfully!',
                    'subscription' => [
                        'id' => $subscription->id,
                        'status' => $subscription->stripe_status,
                        'price_id' => $subscription->stripe_price,
                        'ends_at' => $subscription->ends_at,
                        'trial_ends_at' => $subscription->trial_ends_at
                    ]
                ], 200);
            }

            return response()->json([
                'error' => 'Payment not completed',
                'status' => $session->payment_status
            ], 400);
        } catch (\Exception $e) {
            Log::error('Subscription verification failed', [
                'user_id' => $user->id,
                'session_id' => $request->get('session_id'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to verify subscription: ' . $e->getMessage()
            ], 400);
        }
    }

    public function handleSubscriptionSuccess(Request $request)
    {
        $user = $request->user();

        try {
            $session = $user->stripe()->checkout->sessions->retrieve($request->get('session_id'));

            if ($session->payment_status === 'paid') {
                return redirect()->route('overview')->with('success', 'Subscription activated successfully!');
            }
        } catch (\Exception $e) {
            return redirect()->route('pricing')->with('error', 'There was an error processing your subscription.');
        }
    }

    public function cancel()
    {
        return redirect()->route('pricing')->with('info', 'Subscription checkout was cancelled.');
    }

    public function cancelSubscription(Request $request)
    {
        $user = $request->user();

        try {
            $user->subscription('pro')->cancel();
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
            $user->subscription('pro')->resume();
            return response()->json(['message' => 'Subscription resumed successfully']);
        } catch (\Exception $e) {
            Log::error('Error resuming subscription', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Unable to resume subscription'], 500);
        }
    }

    public function updatePaymentMethod(Request $request)
    {
        $user = $request->user();

        try {
            $user->updateDefaultPaymentMethod($request->input('payment_method_id'));
            return response()->json(['message' => 'Payment method updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update payment method'], 500);
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
