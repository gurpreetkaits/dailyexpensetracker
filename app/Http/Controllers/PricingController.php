<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use NumberFormatter;

class PricingController extends Controller
{
    private $pricingMap = [
        'IN' => [
            'currency' => 'INR',
            'monthly' => 249,
            'yearly' => 2499,
            'locale' => 'en_IN'
        ],
        'US' => [
            'currency' => 'USD',
            'monthly' => 2.99,
            'yearly' => 29.99,
            'locale' => 'en_US'
        ]
    ];

    private $defaultPricing = [
        'currency' => 'USD',
        'monthly' => 2.99,
        'yearly' => 29.99,
        'locale' => 'en_US'
    ];

    public function getPricingData(Request $request)
    {
        $ip = $request->ip();
        $geo = Http::get("https://ipapi.co/{$ip}/json/")->json();
        
        $countryCode = $geo['country_code'] ?? 'US';
        $pricing = $this->pricingMap[$countryCode] ?? $this->defaultPricing;
        
        $formatter = new NumberFormatter($pricing['locale'], NumberFormatter::CURRENCY);
        
        $monthlyPrice = $pricing['monthly'];
        $yearlyPrice = $pricing['yearly'];
        $monthlyEquivalent = $yearlyPrice / 12;

        return [
            'country' => $geo['country_name'] ?? 'United States',
            'currency' => $pricing['currency'],
            'monthly' => [
                'raw' => $monthlyPrice,
                'formatted' => $formatter->formatCurrency($monthlyPrice, $pricing['currency'])
            ],
            'yearly' => [
                'raw' => $yearlyPrice,
                'formatted' => $formatter->formatCurrency($yearlyPrice, $pricing['currency'])
            ],
            'monthly_equivalent' => [
                'raw' => $monthlyEquivalent,
                'formatted' => $formatter->formatCurrency($monthlyEquivalent, $pricing['currency'])
            ]
        ];
    }
} 