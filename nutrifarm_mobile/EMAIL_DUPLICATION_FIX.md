# Fix Email Duplication Check - Backend Implementation

## Problem
Currently, the email duplication error appears at the final registration step instead of when the user first enters their email for verification.

## Solution
The `/auth/send-verification-email` endpoint should check if email exists BEFORE sending the verification code.

## Backend Fix (Laravel)

### 1. Update your `send-verification-email` endpoint:

```php
// In your AuthController or EmailVerificationController

public function sendVerificationEmail(Request $request)
{
    $request->validate([
        'email' => 'required|email',
    ]);

    $email = $request->email;

    // CHECK IF EMAIL ALREADY EXISTS - THIS IS THE KEY FIX
    if (User::where('email', $email)->exists()) {
        return response()->json([
            'success' => false,
            'message' => 'This email is already registered. Please use a different email or try logging in.'
        ], 422); // 422 Unprocessable Entity
    }

    // Generate verification code
    $verificationCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    
    // Store verification code with expiration (e.g., 10 minutes)
    $expiresAt = now()->addMinutes(10);
    
    // Store in database (email_verifications table or cache)
    DB::table('email_verifications')->updateOrInsert(
        ['email' => $email],
        [
            'verification_code' => Hash::make($verificationCode),
            'expires_at' => $expiresAt,
            'created_at' => now(),
            'updated_at' => now(),
        ]
    );

    // Send email
    try {
        Mail::to($email)->send(new VerificationCodeMail($verificationCode));
        
        return response()->json([
            'success' => true,
            'message' => 'Verification code sent to your email',
            'expires_in_minutes' => 10
        ]);
    } catch (\Exception $e) {
        Log::error('Email sending failed: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to send verification email. Please try again.'
        ], 500);
    }
}
```

### 2. Create the email_verifications table migration (if not exists):

```php
// Create migration: php artisan make:migration create_email_verifications_table

public function up()
{
    Schema::create('email_verifications', function (Blueprint $table) {
        $table->id();
        $table->string('email')->index();
        $table->string('verification_code');
        $table->timestamp('expires_at');
        $table->timestamps();
        
        $table->unique('email');
    });
}
```

### 3. Update your registration endpoint:

```php
public function registerWithEmailVerification(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'verification_code' => 'required|string|size:6',
        'name' => 'required|string|max:255',
        'password' => 'required|string|min:8',
        'phone' => 'nullable|string',
    ]);

    // At this point, we know the email doesn't exist (checked in send-verification-email)
    // But let's double-check as a safety measure
    if (User::where('email', $request->email)->exists()) {
        return response()->json([
            'success' => false,
            'message' => 'Email already registered'
        ], 422);
    }

    // Verify the code
    $verification = DB::table('email_verifications')
        ->where('email', $request->email)
        ->where('expires_at', '>', now())
        ->first();

    if (!$verification || !Hash::check($request->verification_code, $verification->verification_code)) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid or expired verification code'
        ], 422);
    }

    // Create user
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'phone' => $request->phone,
        'email_verified_at' => now(),
    ]);

    // Clean up verification record
    DB::table('email_verifications')->where('email', $request->email)->delete();

    // Create token
    $token = $user->createToken('auth-token')->plainTextToken;

    return response()->json([
        'success' => true,
        'message' => 'Registration successful',
        'user' => $user,
        'token' => $token
    ], 201);
}
```

## Flow After Fix:

1. **User enters email** → Frontend calls `/auth/send-verification-email`
2. **Backend checks email exists** → If yes, returns error immediately
3. **Frontend shows error** → "This email is already registered. Please use a different email or try logging in."
4. **User sees error at email input** → User can immediately try a different email

## Testing:

1. Try registering with an existing email
2. Error should appear on the email input page (Register Page)
3. Error should NOT appear on the completion page
4. Try with a new email - should work normally

## Frontend is Already Ready:

Your Flutter app is already set up to handle this properly:
- `EmailService.sendVerificationEmail()` handles 422/409 status codes
- `RegisterPage` displays the error message from backend
- Error appears right below the email input field

The fix is purely on the backend side - just move the email existence check to the `/auth/send-verification-email` endpoint.
