<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\EmailVerification;
use App\Mail\VerificationCodeMail;
use Carbon\Carbon;

class EmailVerificationController extends Controller
{
    /**
     * Send verification email with auto-generated code
     */
    public function sendVerificationEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $email = $request->email;

        try {
            // Generate 6-digit verification code
            $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            
            // Delete old verification codes for this email
            EmailVerification::where('email', $email)->delete();

            // Create new verification record
            EmailVerification::create([
                'email' => $email,
                'verification_code' => $code,
                'expires_at' => now()->addMinutes(10),
            ]);

            // Send email using the same Mailable as AuthController
            Mail::to($email)->send(new VerificationCodeMail($code, $email));

            return response()->json([
                'success' => true,
                'message' => 'Verification code sent to your email',
                'expires_in_minutes' => 10
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send verification email',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify email code
     */
    public function verifyEmailCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'verification_code' => 'required|string|size:6'
        ]);

        try {
            $verification = EmailVerification::where('email', $request->email)
                ->where('verification_code', $request->verification_code)
                ->where('expires_at', '>', Carbon::now())
                ->first();

            if ($verification) {
                // Delete the verification record
                $verification->delete();

                return response()->json([
                    'success' => true,
                    'message' => 'Email verified successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired verification code'
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Verification failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
