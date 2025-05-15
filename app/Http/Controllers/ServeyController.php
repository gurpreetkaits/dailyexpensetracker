<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServeyController extends Controller
{
    public function store(Request $request)
    {
        $user = \App\Models\User::find(Auth::id());
        $surveyData = $request->all();
        $settings = $user->settings ?? [];
        if (is_string($settings)) {
            $settings = json_decode($settings, true) ?: [];
        }
        $settings['survey'] = $surveyData;
        $user->settings = json_encode($settings);
        $user->save();
        return response()->json(['message' => 'Survey saved successfully']);
    }
}
