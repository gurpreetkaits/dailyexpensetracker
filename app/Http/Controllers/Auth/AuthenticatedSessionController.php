<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Setting;
use App\Models\User;
use App\Models\Wallet;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */

    public function store(LoginRequest $request): JsonResponse
    {
        try {
            if ($request->sub) {
                // Handle Google login
                $user = User::where('email', $request->email)->first();

                if (!$user) {
                    // Create new user
                    $user = User::create([
                        'name' => $request->name,
                        'email' => $request->email,
                        'google_id' => $request->sub,
                        'avatar' => $request->picture ?? null,
                        'email_verified_at' => now()
                    ]);
                }
            } else {
                // Regular login
                $request->authenticate();
                $user = Auth::user();
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            if (Setting::where('user_id', $user->id)->doesntExist()) {
                Setting::create([
                    'user_id' => $user->id,
                    'currency_code' => 'USD',
                    'reminders' => 0,
                ]);

                Wallet::create([
                    'user_id' => $user->id,
                    'type' => 'cash',
                    'name' => 'Default',
                    'balance' => 0,
                ]);
            }

            return response()->json([
                'user' => $user,
                'token' => $token,
                'message' => 'Login successful'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Login failed',
                'error' => $e->getMessage()
            ], 422);
        }
    }


    /**
     * Send OTP to email
     */
    public function sendOtp(Request $request): JsonResponse
    {
        if(User::where('email', $request->email)->exists()) {
            return response()->json([
                'message' => 'This account already exists'
            ], 404);
        }
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Invalid email address',
                    'errors' => $validator->errors()
                ], 422);
            }

            $email = $request->email;
            $otp = rand(100000, 999999);

            User::create([
                'email' => $email,
                'name' => explode('@', $email)[0],
                'otp' => $otp
            ]);

            try {
                Mail::raw("Your OTP for Daily Expense Tracker login is: $otp\n\nThis OTP will expire in 5 minutes.", function ($message) use ($email) {
                    $message->to($email)
                           ->subject('Your Login OTP - Daily Expense Tracker');
                });

                return response()->json([
                    'message' => 'OTP sent successfully',
                    'email' => $email
                ]);
            } catch (Exception $e) {
                return response()->json([
                    'message' => 'Failed to send OTP email',
                    'error' => $e->getMessage()
                ], 500);
            }

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to send OTP',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify OTP and login user
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        try {

            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'otp' => 'required|digits:6'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Invalid email or OTP format',
                    'errors' => $validator->errors()
                ], 422);
            }

            $email = $request->email;
            $inputOtp = $request->otp;

            $user = User::where('email', $email)->first();
            if (!$user->otp) {
                return response()->json([
                    'message' => 'OTP expired or not found'
                ], 422);
            }

            if ($user->otp != $inputOtp) {
                return response()->json([
                    'message' => 'Invalid OTP'
                ], 422);
            }
            Cache::forget("otp_$email");

            $user = User::where('email', $email)->first();

            if (!$user) {
                $user = User::create([
                    'name' => explode('@', $email)[0], // Use part before @ as default name
                    'email' => $email,
                    'email_verified_at' => now()
                ]);

                Setting::create([
                    'user_id' => $user->id,
                    'currency_code' => 'USD',
                    'reminders' => 0,
                ]);

                Wallet::create([
                    'user_id' => $user->id,
                    'type' => 'cash',
                    'name' => 'Default',
                    'balance' => 0,
                ]);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'user' => $user,
                'token' => $token,
                'message' => 'Login successful'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'OTP verification failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
