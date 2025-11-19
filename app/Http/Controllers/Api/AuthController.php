<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register new user.
     * MVP: Registration disabled (only Mazen & Maher).
     * MVP 2: Enable registration for others.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        // MVP: Disable registration - only Mazen and Maher exist
        // Set ALLOW_REGISTRATION=true in .env to enable in MVP 2
        if (!config('app.allow_registration', false)) {
            return response()->json([
                'message' => 'Registration is currently disabled. Only Mazen and Maher can use this app.',
            ], 403);
        }

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        // Set cookie with token for persistent sessions (7 days)
        $cookie = cookie(
            'auth_token',
            $token,
            60 * 24 * 7, // 7 days
            '/',
            null,
            config('session.secure', false), // HTTPS in production
            true, // httpOnly
            false, // raw
            config('session.same_site', 'lax')
        );

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
        ], 201)->cookie($cookie);
    }

    /**
     * Login user.
     * Sets token in both response and cookie for persistent sessions.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        // Set cookie with token for persistent sessions (7 days)
        $cookie = cookie(
            'auth_token',
            $token,
            60 * 24 * 7, // 7 days
            '/',
            null,
            config('session.secure', false), // HTTPS in production
            true, // httpOnly
            false, // raw
            config('session.same_site', 'lax')
        );

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
        ])->cookie($cookie);
    }

    /**
     * Get authenticated user.
     */
    public function user(Request $request): JsonResponse
    {
        return response()->json(new UserResource($request->user()));
    }

    /**
     * Logout user.
     * Removes token and clears cookie.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        // Clear auth cookie
        $cookie = cookie()->forget('auth_token');

        return response()->json(['message' => 'Logged out successfully'])->cookie($cookie);
    }
}
