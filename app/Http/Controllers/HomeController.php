<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $pricingData = app(PricingController::class)->getPricingData($request);
        return view('home', [
            'pricingData' => $pricingData
        ]);
    }
} 