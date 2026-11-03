<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ForgotPasswordRequest;
use App\Http\Requests\Admin\LoginRequest;
use App\Http\Requests\Admin\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Handle administrator login API request (Generates Sanctum Token).
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        $user = User::where('email', $credentials['email'])->first();

        if ($user && !$user->isActive()) {
            return response()->json([
                'message' => 'Your administrator account has been deactivated. Please contact the portal administrator.',
                'errors' => [
                    'email' => ['Your account has been deactivated.'],
                ],
            ], 403);
        }

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();
            $user->recordLogin();

            // 🔑 Create Sanctum API Token
            $token = $user->createToken('admin-api-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => "Welcome back, {$user->name}!",
                'token' => $token,         // Return the token here
                'token_type' => 'Bearer',  // Token type specification
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'role_label' => $user->role_label,
                    'status' => $user->status,
                    'last_login_at' => $user->last_login_at ? $user->last_login_at->format('M d, Y - h:i A') : null,
                ],
            ]);
        }

        return response()->json([
            'message' => 'The provided credentials do not match our records.',
            'errors' => [
                'email' => ['The provided credentials do not match our records.'],
            ],
        ], 422);
    }

    /**
     * Get current authenticated administrator info.
     */
    public function me(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['authenticated' => false], 401);
        }

        return response()->json([
            'authenticated' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'role_label' => $user->role_label,
                'status' => $user->status,
                'is_super_admin' => $user->isSuperAdmin(),
                'is_admin' => $user->isAdmin(),
                'last_login_at' => $user->last_login_at ? $user->last_login_at->format('M d, Y - h:i A') : null,
            ],
        ]);
    }

    /**
     * Handle admin logout API request (Revokes Token).
     */
    // public function logout(Request $request): JsonResponse
    // {
    //     // 🔑 Revoke the token currently being used for this request
    //     $request->user()->currentAccessToken()->delete();

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'You have been successfully logged out.',
    //     ]);
    // }

    /**
     * Handle admin logout API request.
     */
    public function logout(Request $request): JsonResponse
    {
        // 1. If using Sanctum API Tokens (Bearer token), delete the current token
        if ($request->user() && method_exists($request->user(), 'currentAccessToken') && $request->user()->currentAccessToken()) {
            $request->user()->currentAccessToken()->delete();
        }

        // 2. If using session-based authentication, invalidate the session
        Auth::guard('web')->logout();
        
        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json([
            'success' => true,
            'message' => 'You have been successfully logged out.',
        ]);
    }

    /**
     * Handle forgot password request.
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if ($user && !$user->isActive()) {
            return response()->json([
                'message' => 'This account is inactive. Please contact the administrator.',
                'errors' => ['email' => ['This account is inactive.']],
            ], 403);
        }

        $status = Password::broker()->sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'success' => true,
                'message' => 'We have sent your password reset link to your email.',
            ]);
        }

        return response()->json([
            'message' => __($status),
            'errors' => ['email' => [__($status)]],
        ], 422);
    }

    /**
     * Handle reset password request.
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $status = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'success' => true,
                'message' => 'Your password has been reset successfully. You may now sign in.',
            ]);
        }

        return response()->json([
            'message' => __($status),
            'errors' => ['email' => [__($status)]],
        ], 422);
    }
}