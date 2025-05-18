<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Setting;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function store(LoginRequest $request): JsonResponse
    {
        try {
            // 1️⃣ Google login (ID token flow)
            if ($request->filled('sub')) {
                // find or create by email
                $user = User::firstOrCreate(
                    ['email' => $request->email],
                    [
                        'name'              => $request->name,
                        'google_id'         => $request->sub,
                        'avatar'            => $request->picture ?? null,
                        'email_verified_at' => now(),
                    ]
                );
            }
            else {
                if (! Auth::attempt($request->only('email','password'))) {
                    return response()->json([
                        'message' => 'Invalid credentials'
                    ], 401);
                }
                $user = Auth::user();
            }

            if(!$user){
                throw new Exception('User not found');
            }
            $plainToken = $user->createToken('mobile')->plainTextToken;

            $user->remember_token = $plainToken;
            $user->save();

            // 5️⃣ Ensure a default settings row exists
            Setting::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'currency_code' => 'USD',
                    'reminders'     => 0,
                ]
            );

            return response()->json([
                'user'    => $user,
                'token'   => $plainToken,
                'message' => 'Login successful',
            ]);
        }
        catch (\Exception $e) {
            return response()->json([
                'message' => 'Login failed',
                'error'   => $e->getMessage(),
            ], 422);
        }
    }
}
