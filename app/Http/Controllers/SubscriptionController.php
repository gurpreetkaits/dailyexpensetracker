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

    public function createCheckoutSession(Request $request)
    {
        $user = $request->user();
        $priceId = 'price_1RNQExSHibvmemb3Cpqa41EM';

        if (!$priceId) {
            Log::error('Missing price ID in checkout request', [
                'user_id' => $user->id,
                'request_data' => $request->all()
            ]);
            return response()->json(['error' => 'Price ID is required'], 400);
        }

        try {
            // Verify the price exists in Stripe
//            try {
//                $price = Price::retrieve($priceId);
//                Log::info('Price verified in Stripe', [
//                    'price_id' => $priceId,
//                    'price_details' => [
//                        'amount' => $price->unit_amount,
//                        'currency' => $price->currency,
//                        'recurring' => $price->recurring
//                    ]
//                ]);
//            } catch (\Stripe\Exception\InvalidRequestException $e) {
//                Log::error('Invalid price ID', [
//                    'price_id' => $priceId,
//                    'error' => $e->getMessage()
//                ]);
//                return response()->json(['error' => 'Invalid price ID'], 400);
//            }
//
//            $frontendUrl = config('app.frontend_url', 'http://localhost:5173');
//
//            Log::info('Creating checkout session', [
//                'user_id' => $user->id,
//                'price_id' => $priceId,
//                'frontend_url' => $frontendUrl
//            ]);
            $frontendUrl = env('FRONTEND_URL');
            $checkout = $user->newSubscription('default', $priceId)
                ->allowPromotionCodes()
                ->checkout([
                    'success_url' => $request->header('Referer') . '?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url' => $request->header('Referer'),
                    'billing_address_collection' => 'required',
                    'metadata' => [
                        'user_id' => $user->id,
                        'price_id' => 'price_premium'
                    ]
                ]);

            Log::info('Stripe checkout session created successfully', [
                'user_id' => $user->id,
                'session_id' => $checkout->id,
                'price_id' => $priceId
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
        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::error('Stripe API error', [
                'user_id' => $user->id,
                'error_type' => $e->getStripeCode(),
                'error_message' => $e->getMessage(),
                'http_status' => $e->getHttpStatus()
            ]);

            return response()->json([
                'error' => 'Stripe API error: ' . $e->getMessage()
            ], 500);
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
            'hasActiveSubscription' => $user->subscribed('default'),
            'subscription' => $user->subscription('default'),
            'defaultPaymentMethod' => $user->defaultPaymentMethod()
        ]);
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
            $user->subscription('default')->cancel();
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
            $user->subscription('default')->resume();
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
}
