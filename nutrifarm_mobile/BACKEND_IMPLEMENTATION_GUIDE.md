# Nutrifarm Mobile App - Backend Implementation Guide

## Overview

This guide provides complete implementation instructions for the backend API to support the Nutrifarm mobile app's email registration and authentication system.

## Technology Stack

-   **Framework**: Laravel 10+ (PHP)
-   **Authentication**: Laravel Sanctum (API tokens)
-   **Database**: MySQL
-   **Email**: Laravel Mail with SMTP
-   **API Format**: RESTful JSON API

## Architecture Overview

The mobile app implements a **3-step authentication flow**:

1. **Email Validation & Verification**: User enters email → system validates email is not already taken → sends verification code
2. **Complete Registration**: User enters name, password, optional phone (email already validated)
3. **Login**: User can login with email/password for future sessions

### Why Email Validation Happens in Step 1

✅ **Better User Experience**: Users know immediately if email is available
✅ **Prevents Wasted Resources**: No verification codes sent to invalid emails
✅ **Security**: Prevents spamming existing users with verification codes
✅ **Clear Error Messages**: Validation errors happen at the right time

### Email Validation Flow

```
User enters email → Backend validates:
├── Email format valid? ❌ → Return format error
├── Email already exists? ❌ → Return "email already taken"
└── All valid? ✅ → Send verification code
```

## Required Database Tables

### 1. Users Table

```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### 2. Email Verifications Table

```sql
CREATE TABLE email_verifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    verification_code VARCHAR(10) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    verified_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_code (verification_code),
    INDEX idx_expires (expires_at)
);
```

### 3. Personal Access Tokens Table (Laravel Sanctum)

```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

## Required API Endpoints

### Base URL

```
https://your-domain.com/api
```

### Authentication Headers

```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {token} (for authenticated endpoints)
```

## 1. Email Verification Endpoints

### Send Verification Email

**POST** `/api/send-verification-email`

**Request Body:**

```json
{
    "email": "user@example.com"
}
```

**Response (Success - 200):**

```json
{
    "success": true,
    "message": "Verification code sent to your email",
    "expires_in_minutes": 10
}
```

**Response (Error - 422):**

```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "email": ["The email has already been taken."]
    }
}
```

**Implementation:**

```php
<?php
// app/Http/Controllers/AuthController.php

public function sendVerificationEmail(Request $request)
{
    $request->validate([
        'email' => 'required|email|max:255|unique:users,email'
    ]);

    $email = $request->email;

    // Generate 4-digit code
    $code = str_pad(random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
    $expiresAt = now()->addMinutes(10);

    // Delete old codes for this email
    EmailVerification::where('email', $email)->delete();

    // Store new verification code
    EmailVerification::create([
        'email' => $email,
        'verification_code' => $code,
        'expires_at' => $expiresAt,
    ]);

    // Send email
    Mail::to($email)->send(new VerificationCodeMail($code));

    return response()->json([
        'success' => true,
        'message' => 'Verification code sent to your email',
        'expires_in_minutes' => 10
    ]);
}
```

### Verify Email Code

**POST** `/api/verify-email-code`

**Request Body:**

```json
{
    "email": "user@example.com",
    "verification_code": "1234"
}
```

**Response (Success - 200):**

```json
{
    "success": true,
    "message": "Email verified successfully"
}
```

**Response (Error - 422):**

```json
{
    "success": false,
    "message": "Invalid or expired verification code"
}
```

**Implementation:**

```php
public function verifyEmailCode(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'verification_code' => 'required|string|size:4'
    ]);

    $verification = EmailVerification::where('email', $request->email)
        ->where('verification_code', $request->verification_code)
        ->where('expires_at', '>', now())
        ->whereNull('verified_at')
        ->first();

    if (!$verification) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid or expired verification code'
        ], 422);
    }

    // Mark as verified
    $verification->update(['verified_at' => now()]);

    return response()->json([
        'success' => true,
        'message' => 'Email verified successfully'
    ]);
}
```

## 2. Registration Endpoint

### Complete Registration (After Email Verification)

**POST** `/api/register-with-email-verification`

**Request Body:**

```json
{
    "email": "user@example.com",
    "verification_code": "1234",
    "name": "John Doe",
    "password": "password123",
    "password_confirmation": "password123",
    "phone": "+1234567890"
}
```

**Response (Success - 201):**

```json
{
    "success": true,
    "message": "Registration successful",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "user@example.com",
        "phone": "+1234567890",
        "email_verified_at": "2025-08-09T10:30:00.000000Z",
        "created_at": "2025-08-09T10:30:00.000000Z",
        "updated_at": "2025-08-09T10:30:00.000000Z"
    },
    "token": "1|abc123def456ghi789..."
}
```

**Response (Error - 422):**

```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "password": ["The password must be at least 8 characters."]
    }
}
```

**Implementation:**

```php
public function registerWithEmailVerification(Request $request)
{
    $request->validate([
        'email' => 'required|email', // Remove unique validation here
        'verification_code' => 'required|string|size:4',
        'name' => 'required|string|max:255',
        'password' => 'required|string|min:8|confirmed',
        'phone' => 'nullable|string|max:20'
    ]);

    // Verify the email verification code
    $verification = EmailVerification::where('email', $request->email)
        ->where('verification_code', $request->verification_code)
        ->where('expires_at', '>', now())
        ->whereNull('verified_at')
        ->first();

    if (!$verification) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid or expired verification code'
        ], 422);
    }

    // Double-check email uniqueness before creating user
    if (User::where('email', $request->email)->exists()) {
        return response()->json([
            'success' => false,
            'message' => 'Email address is already registered'
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

    // Mark verification as used
    $verification->update(['verified_at' => now()]);

    // Create token
    $token = $user->createToken('mobile-app')->plainTextToken;

    return response()->json([
        'success' => true,
        'message' => 'Registration successful',
        'user' => $user,
        'token' => $token
    ], 201);
}
```

## 3. Login Endpoint

### User Login

**POST** `/api/login`

**Request Body:**

```json
{
    "email": "user@example.com",
    "password": "password123"
}
```

**Response (Success - 200):**

```json
{
    "success": true,
    "message": "Login successful",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "user@example.com",
        "phone": "+1234567890",
        "email_verified_at": "2025-08-09T10:30:00.000000Z",
        "created_at": "2025-08-09T10:30:00.000000Z",
        "updated_at": "2025-08-09T10:30:00.000000Z"
    },
    "token": "2|xyz789abc123def456..."
}
```

**Response (Error - 401):**

```json
{
    "success": false,
    "message": "Invalid email or password"
}
```

**Implementation:**

```php
public function login(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required|string'
    ]);

    if (!Auth::attempt($request->only('email', 'password'))) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid email or password'
        ], 401);
    }

    $user = Auth::user();
    $token = $user->createToken('mobile-app')->plainTextToken;

    return response()->json([
        'success' => true,
        'message' => 'Login successful',
        'user' => $user,
        'token' => $token
    ]);
}
```

## 4. Profile Management Endpoints

### Get User Profile

**GET** `/api/profile`
**Headers:** `Authorization: Bearer {token}`

**Response (Success - 200):**

```json
{
    "success": true,
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "user@example.com",
        "phone": "+1234567890",
        "email_verified_at": "2025-08-09T10:30:00.000000Z",
        "created_at": "2025-08-09T10:30:00.000000Z",
        "updated_at": "2025-08-09T10:30:00.000000Z"
    }
}
```

### Update Profile

**PUT** `/api/profile`
**Headers:** `Authorization: Bearer {token}`

**Request Body:**

```json
{
    "name": "John Smith",
    "phone": "+1987654321"
}
```

**Response (Success - 200):**

```json
{
    "success": true,
    "message": "Profile updated successfully",
    "user": {
        "id": 1,
        "name": "John Smith",
        "email": "user@example.com",
        "phone": "+1987654321",
        "email_verified_at": "2025-08-09T10:30:00.000000Z",
        "created_at": "2025-08-09T10:30:00.000000Z",
        "updated_at": "2025-08-09T10:31:00.000000Z"
    }
}
```

### Logout

**POST** `/api/logout`
**Headers:** `Authorization: Bearer {token}`

**Response (Success - 200):**

```json
{
    "success": true,
    "message": "Logged out successfully"
}
```

## 5. Required Models

### User Model

```php
<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}
```

### EmailVerification Model

```php
<?php
// app/Models/EmailVerification.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'verification_code',
        'expires_at',
        'verified_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];
}
```

## 6. Email Template

### Verification Code Email

```php
<?php
// app/Mail/VerificationCodeMail.php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerificationCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $verificationCode;

    public function __construct($verificationCode)
    {
        $this->verificationCode = $verificationCode;
    }

    public function build()
    {
        return $this->subject('Your Nutrifarm Store Verification Code')
                   ->view('emails.verification-code')
                   ->with(['code' => $this->verificationCode]);
    }
}
```

### Email Template (Blade)

```html
<!-- resources/views/emails/verification-code.blade.php -->
<!DOCTYPE html>
<html>
    <head>
        <title>Email Verification</title>
        <style>
            .container {
                max-width: 600px;
                margin: 0 auto;
                font-family: Arial, sans-serif;
            }
            .header {
                background: #4caf50;
                color: white;
                padding: 20px;
                text-align: center;
            }
            .content {
                padding: 30px;
                background: #f9f9f9;
            }
            .code-box {
                background: white;
                border: 2px solid #4caf50;
                padding: 20px;
                text-align: center;
                font-size: 24px;
                font-weight: bold;
                letter-spacing: 5px;
                margin: 20px 0;
            }
            .footer {
                text-align: center;
                padding: 20px;
                color: #666;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>Nutrifarm Store</h1>
            </div>
            <div class="content">
                <h2>Email Verification Required</h2>
                <p>
                    Thank you for signing up with Nutrifarm Store! Please use
                    the verification code below to complete your registration:
                </p>

                <div class="code-box">{{ $code }}</div>

                <p><strong>This code will expire in 10 minutes.</strong></p>
                <p>
                    If you didn't request this code, please ignore this email.
                </p>
            </div>
            <div class="footer">
                <p>&copy; 2025 Nutrifarm Store. All rights reserved.</p>
            </div>
        </div>
    </body>
</html>
```

## 7. Environment Configuration

### .env Settings

```env
# App Settings
APP_URL=https://your-domain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nutrifarm_db
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Mail Settings (Gmail SMTP Example)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="Nutrifarm Store"

# Sanctum
SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1,your-domain.com
SESSION_DRIVER=cookie
```

## 8. Routes Configuration

### API Routes

```php
<?php
// routes/api.php

use App\Http\Controllers\AuthController;

Route::prefix('api')->group(function () {
    // Email verification routes
    Route::post('/send-verification-email', [AuthController::class, 'sendVerificationEmail']);
    Route::post('/verify-email-code', [AuthController::class, 'verifyEmailCode']);

    // Authentication routes
    Route::post('/register-with-email-verification', [AuthController::class, 'registerWithEmailVerification']);
    Route::post('/login', [AuthController::class, 'login']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});
```

## 9. Installation Steps

### 1. Create Laravel Project

```bash
composer create-project laravel/laravel nutrifarm-backend
cd nutrifarm-backend
```

### 2. Install Sanctum

```bash
composer install laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

### 3. Create Migrations

```bash
php artisan make:migration create_email_verifications_table
php artisan migrate
```

### 4. Create Controllers and Models

```bash
php artisan make:controller AuthController
php artisan make:model EmailVerification
php artisan make:mail VerificationCodeMail
```

### 5. Configure CORS (if needed)

```php
// config/cors.php
'allowed_origins' => ['*'],
'allowed_methods' => ['*'],
'allowed_headers' => ['*'],
```

## 10. Testing the Implementation

### Test Registration Flow

1. **Send verification email**: POST `/api/send-verification-email`
2. **Check email**: Verify code is received
3. **Verify email**: POST `/api/verify-email-code`
4. **Complete registration**: POST `/api/register-with-email-verification`
5. **Test login**: POST `/api/login`

### Test Authentication

1. **Login**: POST `/api/login`
2. **Get profile**: GET `/api/profile` (with token)
3. **Update profile**: PUT `/api/profile` (with token)
4. **Logout**: POST `/api/logout` (with token)

## 11. Security Considerations

-   ✅ **Email Validation First**: Check email uniqueness before sending codes
-   ✅ **Rate Limiting**: Implement rate limiting on email sending (max 3 attempts per hour per email)
-   ✅ **CSRF Protection**: Sanctum provides CSRF protection
-   ✅ **Password Hashing**: Laravel automatically hashes passwords
-   ✅ **Token Expiration**: Set appropriate token expiration
-   ✅ **Code Expiration**: Verification codes expire in 10 minutes
-   ✅ **Code Cleanup**: Automatically delete expired verification codes
-   ✅ **Prevent Code Reuse**: Mark codes as used after verification

### Additional Security Measures

```php
// Rate limiting example in RouteServiceProvider
RateLimiter::for('email-verification', function (Request $request) {
    return Limit::perHour(3)->by($request->input('email'));
});

// In your controller
public function sendVerificationEmail(Request $request)
{
    // Apply rate limiting
    if (RateLimiter::tooManyAttempts('email-verification:'.$request->input('email'), 3)) {
        return response()->json([
            'success' => false,
            'message' => 'Too many verification attempts. Please try again later.'
        ], 429);
    }
    
    // ... rest of implementation
}

## 12. Mobile App Configuration

Once your backend is ready, update the Flutter app's base URL:

```dart
// lib/services/api_service.dart
static const String baseUrl = 'http://127.0.0.1:8000/api';

// lib/services/auth_service.dart  
static const String _baseUrl = 'http://127.0.0.1:8000/api';

// lib/services/email_service.dart
static const String _baseUrl = 'http://127.0.0.1:8000/api';

// lib/models/product.dart (for image URLs)
return 'http://127.0.0.1:8000/storage/$imagePath';
```

**Note**: Your backend is running on port **8000**, so all URLs have been updated accordingly.

### Your Actual Backend Endpoints:

**Authentication:**
- POST `http://127.0.0.1:8000/api/auth/send-verification-email`
- POST `http://127.0.0.1:8000/api/auth/login`

**Products:**
- GET `http://127.0.0.1:8000/api/products`
- POST `http://127.0.0.1:8000/api/products`

**Cart:**
- GET `http://127.0.0.1:8000/api/cart`
- POST `http://127.0.0.1:8000/api/cart`
- PUT `http://127.0.0.1:8000/api/cart/{id}` (update quantity)
- DELETE `http://127.0.0.1:8000/api/cart/{id}` (remove item)
- DELETE `http://127.0.0.1:8000/api/cart/clear` (clear cart)

**Orders:**
- GET `http://127.0.0.1:8000/api/orders`
- POST `http://127.0.0.1:8000/api/orders`
- GET `http://127.0.0.1:8000/api/orders/{id}`
- PUT `http://127.0.0.1:8000/api/orders/{id}/cancel`

**Additional Endpoints for Full Functionality:**

**Favorites (if needed):**
- GET `http://127.0.0.1:8000/api/favorites`
- POST `http://127.0.0.1:8000/api/favorites`
- DELETE `http://127.0.0.1:8000/api/favorites/{id}`
- POST `http://127.0.0.1:8000/api/favorites/toggle`

**Profile Management:**
- GET `http://127.0.0.1:8000/api/profile` (or `/user`)
- PUT `http://127.0.0.1:8000/api/profile`
- POST `http://127.0.0.1:8000/api/logout`

**Email Verification (already mentioned):**
- POST `http://127.0.0.1:8000/api/auth/verify-email-code`
- POST `http://127.0.0.1:8000/api/auth/register-with-email-verification`

## Success Criteria

After implementation, users should be able to:

1. ✅ Enter email and receive verification code
2. ✅ Verify email with the received code
3. ✅ Complete registration with name and password
4. ✅ Login with email/password
5. ✅ Access protected features with authentication token
6. ✅ Update their profile information
7. ✅ Logout and clear authentication

## Support

If you need clarification on any endpoint or implementation detail, refer to this documentation or contact the mobile development team.

---

**Backend Implementation Checklist:**

-   [ ] Set up Laravel project with Sanctum
-   [ ] Create database tables (users, email_verifications)
-   [ ] Implement email verification endpoints
-   [ ] Implement registration endpoint
-   [ ] Implement login/logout endpoints
-   [ ] Implement profile management endpoints
-   [ ] Configure email sending (SMTP)
-   [ ] Create email templates
-   [ ] Test all endpoints
-   [ ] Deploy to production server
-   [ ] Update mobile app with production URL
