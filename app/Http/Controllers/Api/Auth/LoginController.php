<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $request->login)
            ->orWhere('email', $request->login)
            ->first();

        // Check if user exists, password is correct, AND is_show = 1
        if (!$user || !Hash::check($request->password, $user->password) || $user->is_show != 1) {
            throw ValidationException::withMessages([
                'login' => ['The provided credentials are incorrect or account is disabled.'],
            ]);
        }

        // Auto generate remember_token on every login for persistent auth
        $rememberToken = Str::random(60);
        $user->setRememberToken($rememberToken);
        $user->save();

        // Generate new API token
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
            'remember_token' => $rememberToken,
            'message' => 'Login successful'
        ]);
    }

    public function autoLogin(Request $request)
    {
        $request->validate([
            'remember_token' => 'required|string',
        ]);

        $user = User::where('remember_token', $request->remember_token)
            ->where('is_show', 1) // Add is_show check for auto-login too
            ->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'remember_token' => ['Invalid remember token or account is disabled. Please login again.'],
            ]);
        }

        // Rotate remember_token for security on successful auto-login
        $newRememberToken = Str::random(60);
        $user->setRememberToken($newRememberToken);
        $user->save();

        // Generate new API token
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
            'remember_token' => $newRememberToken,
            'message' => 'Auto-login successful'
        ]);
    }

    public function logout(Request $request)
    {
        if (!$request->user()) {
            return response()->json([
                'message' => 'Unauthenticated. No user found for the provided token.'
            ], 401);
        }

        $user = $request->user();

        // Clear remember_token to prevent future auto-logins
        $user->setRememberToken(null);
        $user->save();

        // Delete current API token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
}