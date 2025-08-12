<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Models\EmailVerification;

class PasswordlessAuthController extends Controller
{
    /**
     * Login or register user after email verification
     * This endpoint is called after successful email verification
     */
    public function loginAfterEmailVerification(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->email;

        try {
            // Try to find existing user
            $user = User::where('email', $email)->first();

            if ($user) {
                // Existing user - generate token
                $token = $user->createToken('passwordless-login')->plainTextToken;

                return response()->json([
                    'success' => true,
                    'token' => $token,
                    'user' => $user,
                    'message' => 'Welcome back!',
                    'is_new_user' => false,
                ]);
            } else {
                // New user - create account
                $name = $this->extractNameFromEmail($email);
                
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make('email-verified-' . time()), // Random password they'll never use
                    'email_verified_at' => now(), // Mark as verified since they verified via email
                ]);

                $token = $user->createToken('passwordless-login')->plainTextToken;

                return response()->json([
                    'success' => true,
                    'token' => $token,
                    'user' => $user,
                    'message' => 'Account created and logged in!',
                    'is_new_user' => true,
                ], 201);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verify email code and automatically login
     * This combines email verification + login into one step
     */
    public function verifyEmailAndLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'verification_code' => 'required|string|size:4',
        ]);

        try {
            // First verify the email code
            $verification = EmailVerification::where('email', $request->email)
                ->where('verification_code', $request->verification_code)
                ->where('is_verified', false)
                ->where('expires_at', '>', now())
                ->first();

            if (!$verification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired verification code',
                ], 400);
            }

            // Mark verification as used
            $verification->update(['is_verified' => true]);
            $verification->delete(); // Clean up

            // Now login or create user
            $user = User::where('email', $request->email)->first();

            if ($user) {
                // Existing user - login
                $token = $user->createToken('passwordless-login')->plainTextToken;

                return response()->json([
                    'success' => true,
                    'token' => $token,
                    'user' => $user,
                    'message' => 'Welcome back!',
                    'is_new_user' => false,
                ]);
            } else {
                // New user - create and login
                $name = $this->extractNameFromEmail($request->email);
                
                $user = User::create([
                    'name' => $name,
                    'email' => $request->email,
                    'password' => Hash::make('email-verified-' . time()),
                    'email_verified_at' => now(),
                ]);

                $token = $user->createToken('passwordless-login')->plainTextToken;

                return response()->json([
                    'success' => true,
                    'token' => $token,
                    'user' => $user,
                    'message' => 'Welcome to Nutrifarm!',
                    'is_new_user' => true,
                ], 201);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if email exists (optional - for UI hints)
     */
    public function checkEmailExists(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $exists = User::where('email', $request->email)->exists();

        return response()->json([
            'success' => true,
            'data' => [
                'email_exists' => $exists,
                'message' => $exists 
                    ? 'We\'ll send a login code to this email'
                    : 'We\'ll create your account and send a verification code',
            ],
        ]);
    }

    /**
     * Extract a reasonable name from email address
     */
    private function extractNameFromEmail($email)
    {
        $username = explode('@', $email)[0];
        
        // Replace dots, dashes, underscores with spaces and title case
        $name = str_replace(['.', '-', '_'], ' ', $username);
        $name = ucwords(strtolower($name));
        
        return $name;
    }

    /**
     * Enhanced logout (revoke current token)
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get current user with additional profile info
     */
    public function me(Request $request)
    {
        try {
            $user = $request->user();
            
            // Add additional user stats
            $userWithStats = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
                'stats' => [
                    'cart_items' => $user->cartItems()->count(),
                    'favorites' => $user->favorites()->count(),
                    'orders' => $user->orders()->count(),
                ],
            ];

            return response()->json([
                'success' => true,
                'user' => $userWithStats,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get user profile',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
