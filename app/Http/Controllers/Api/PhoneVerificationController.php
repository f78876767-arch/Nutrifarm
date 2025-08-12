<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PhoneVerificationController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Send verification code to phone number
     */
    public function sendCode(Request $request): JsonResponse
    {
        $request->validate([
            'phone_number' => 'required|string|regex:/^\+[1-9]\d{1,14}$/', // E.164 format
        ]);

        $result = $this->smsService->sendVerificationCode($request->phone_number);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
        ], $result['success'] ? 200 : 400);
    }

    /**
     * Verify the received code
     */
    public function verifyCode(Request $request): JsonResponse
    {
        $request->validate([
            'phone_number' => 'required|string|regex:/^\+[1-9]\d{1,14}$/',
            'verification_code' => 'required|string|size:6',
        ]);

        $result = $this->smsService->verifyCode(
            $request->phone_number,
            $request->verification_code
        );

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
        ], $result['success'] ? 200 : 400);
    }

    /**
     * Check if phone number is verified
     */
    public function checkVerification(Request $request): JsonResponse
    {
        $request->validate([
            'phone_number' => 'required|string|regex:/^\+[1-9]\d{1,14}$/',
        ]);

        $isVerified = $this->smsService->isPhoneVerified($request->phone_number);

        return response()->json([
            'success' => true,
            'is_verified' => $isVerified,
        ]);
    }
}
