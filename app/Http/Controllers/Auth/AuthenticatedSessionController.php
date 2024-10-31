<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
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

            Setting::updateOrCreate(['user_id' => $user->id], [
                'currency_code' => 'USD',
                'reminders' => 0,
            ]);
            return response()->json([
                'user' => $user,
                'token' => $token,
                'message' => 'Login successful'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Login failed',
                'error' => $e->getMessage()
            ], 422);
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
