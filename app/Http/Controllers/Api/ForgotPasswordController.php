<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Mail\ResetPasswordMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class ForgotPasswordController extends Controller
{
    // POST /api/auth/forgot-password
    public function send(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // Simple per-email rate limit (in addition to route throttle)
        $key = 'fp:' . Str::lower($request->email) . ':' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json(['success' => true, 'message' => 'If the email exists, a reset link has been sent.']);
        }
        RateLimiter::hit($key, 1800); // 30 minutes

        try {
            $user = User::where('email', $request->email)->first();
            if ($user) {
                $token = Password::broker()->createToken($user);
                // Send email with token for mobile app usage
                Mail::to($user->email)->send(new ResetPasswordMail($user, $token));
            }
        } catch (\Throwable $e) {
            Log::warning('forgot_password.send_failed', ['email' => $request->email, 'error' => $e->getMessage()]);
            // Do not leak any info
        }

        return response()->json(['success' => true, 'message' => 'If the email exists, a reset link has been sent.']);
    }

    // POST /api/auth/reset-password
    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) use ($request) {
                $user->forceFill(['password' => Hash::make($password)])->save();
                // Invalidate all tokens (Sanctum)
                if (method_exists($user, 'tokens')) {
                    $user->tokens()->delete();
                }
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['success' => true, 'message' => 'Password reset successful']);
        }

        return response()->json(['success' => false, 'message' => 'Invalid or expired token'], 400);
    }
}
