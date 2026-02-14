<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use App\Models\Wallet;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GoogleAuthController extends Controller
{
    /**
     * Handle Google OAuth callback - exchange code for token server-side
     * This keeps client_secret secure on the server
     */
    public function callback(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'code' => 'required|string',
                'redirect_uri' => 'required|string',
            ]);

            // Exchange authorization code for access token (server-side)
            $tokenResponse = Http::post('https://oauth2.googleapis.com/token', [
                'code' => $request->code,
                'client_id' => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
                'redirect_uri' => $request->redirect_uri,
                'grant_type' => 'authorization_code',
            ]);

            if (!$tokenResponse->successful()) {
                return response()->json([
                    'message' => 'Failed to exchange authorization code',
                    'error' => $tokenResponse->json()
                ], 422);
            }

            $accessToken = $tokenResponse->json('access_token');

            // Get user info from Google
            $userInfoResponse = Http::withHeaders([
                'Authorization' => "Bearer {$accessToken}"
            ])->get('https://www.googleapis.com/oauth2/v3/userinfo');

            if (!$userInfoResponse->successful()) {
                return response()->json([
                    'message' => 'Failed to get user info from Google'
                ], 422);
            }

            $googleUser = $userInfoResponse->json();

            // Find or create user
            $user = User::where('email', $googleUser['email'])->first();

            if (!$user) {
                $user = User::create([
                    'name' => $googleUser['name'],
                    'email' => $googleUser['email'],
                    'google_id' => $googleUser['sub'],
                    'avatar' => $googleUser['picture'] ?? null,
                    'email_verified_at' => now()
                ]);
            } else {
                // Update google_id if not set
                if (!$user->google_id) {
                    $user->update([
                        'google_id' => $googleUser['sub'],
                        'avatar' => $googleUser['picture'] ?? $user->avatar,
                    ]);
                }
            }

            // Create auth token
            $token = $user->createToken('auth_token')->plainTextToken;

            // Ensure settings exist
            if (Setting::where('user_id', $user->id)->doesntExist()) {
                Setting::create([
                    'user_id' => $user->id,
                    'currency_code' => 'USD',
                    'reminders' => 0,
                ]);
            }

            // Ensure wallet exists
            if (Wallet::where('user_id', $user->id)->doesntExist()) {
                Wallet::create([
                    'user_id' => $user->id,
                    'type' => 'cash',
                    'name' => 'Default',
                    'balance' => 0,
                    'currency' => 'USD',
                ]);
            }

            return response()->json([
                'user' => $user,
                'token' => $token,
                'message' => 'Login successful'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Google authentication failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
