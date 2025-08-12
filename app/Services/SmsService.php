<?php

namespace App\Services;

use Twilio\Rest\Client;
use App\Models\PhoneVerification;
use Carbon\Carbon;
use Exception;

class SmsService
{
    protected $twilio;
    protected $fromNumber;

    public function __construct()
    {
        $this->twilio = new Client(
            config('services.twilio.sid'),
            config('services.twilio.auth_token')
        );
        $this->fromNumber = config('services.twilio.from_number');
    }

    /**
     * Send verification code to phone number
     */
    public function sendVerificationCode(string $phoneNumber): array
    {
        try {
            // Clean up expired verifications for this phone number
            PhoneVerification::where('phone_number', $phoneNumber)
                ->where('expires_at', '<', now())
                ->delete();

            // Generate new verification code
            $code = PhoneVerification::generateCode();
            
            // Save verification record
            PhoneVerification::create([
                'phone_number' => $phoneNumber,
                'verification_code' => $code,
                'expires_at' => Carbon::now()->addMinutes(5), // 5 minutes expiry
                'is_verified' => false,
            ]);

            // Send SMS via Twilio
            $message = $this->twilio->messages->create(
                $phoneNumber,
                [
                    'from' => $this->fromNumber,
                    'body' => "Your Nutrifarm verification code is: {$code}. Valid for 5 minutes."
                ]
            );

            return [
                'success' => true,
                'message' => 'Verification code sent successfully',
                'message_sid' => $message->sid
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to send SMS: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verify the code sent to phone number
     */
    public function verifyCode(string $phoneNumber, string $code): array
    {
        try {
            $verification = PhoneVerification::where('phone_number', $phoneNumber)
                ->where('verification_code', $code)
                ->where('is_verified', false)
                ->first();

            if (!$verification) {
                return [
                    'success' => false,
                    'message' => 'Invalid verification code'
                ];
            }

            if ($verification->isExpired()) {
                return [
                    'success' => false,
                    'message' => 'Verification code has expired'
                ];
            }

            // Mark as verified
            $verification->update(['is_verified' => true]);

            return [
                'success' => true,
                'message' => 'Phone number verified successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Verification failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check if phone number is verified
     */
    public function isPhoneVerified(string $phoneNumber): bool
    {
        return PhoneVerification::where('phone_number', $phoneNumber)
            ->where('is_verified', true)
            ->exists();
    }
}
