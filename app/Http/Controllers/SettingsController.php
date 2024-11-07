<?php

namespace App\Http\Controllers;

use App\Http\Resources\SettingsResource;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::with('currency')->where('user_id', auth()->id())->get();
        return response()->json(SettingsResource::collection($settings));
    }

    public function store(Request $request)
    {
        $request->validate([
            'reminders' => 'nullable',
            'currency_code' => 'required',
            'income' => 'nullable|integer',
        ]);

        $settings = Setting::updateOrCreate([
            'user_id' => auth()->id(),
        ], [
            'reminders' => $request->input('reminders'),
            'currency_code' => $request->input('currency_code'),
        ]);
        
        User::where('id', auth()->id())->update([
            'income'=> $request->input('income'),
        ]);

        $settings->save();
        return response()->json(['message' => 'Settings Saved'], 200);
    }
}
