<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Paddle Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the environment your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */
    'environment' => env('PADDLE_ENVIRONMENT', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | Paddle Vendor ID
    |--------------------------------------------------------------------------
    |
    | This is your Paddle vendor ID. You can find this in your Paddle dashboard
    | under Developer Tools > Authentication.
    |
    */
    'vendor_id' => env('PADDLE_VENDOR_ID'),

    /*
    |--------------------------------------------------------------------------
    | Paddle API Key
    |--------------------------------------------------------------------------
    |
    | This is your Paddle API key. You can find this in your Paddle dashboard
    | under Developer Tools > Authentication.
    |
    */
    'api_key' => env('PADDLE_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Paddle Public Key
    |--------------------------------------------------------------------------
    |
    | This is your Paddle public key used for client-side verification.
    | You can find this in your Paddle dashboard under Developer Tools.
    |
    */
    'public_key' => env('PADDLE_PUBLIC_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Subscription Plans
    |--------------------------------------------------------------------------
    |
    | Here you may define the subscription plans that your application offers
    | to customers. The Paddle plan ID should be the ID of the plan in your
    | Paddle dashboard.
    |
    */
    'plans' => [
        'premium_monthly' => [
            'name' => 'Premium Monthly',
            'description' => 'Unlimited access to all premium features',
            'price' => 9.99,
            'interval' => 'month',
            'paddle_plan_id' => env('PADDLE_PREMIUM_MONTHLY_PLAN_ID'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Settings
    |--------------------------------------------------------------------------
    |
    | These settings configure the webhook handling for Paddle events.
    |
    */
    'webhook' => [
        'secret' => env('PADDLE_WEBHOOK_SECRET'),
    ],
]; 