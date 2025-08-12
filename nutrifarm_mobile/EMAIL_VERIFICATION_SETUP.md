# Authentication & Email Verification API Setup

Complete API endpoints needed for user registration, email verification, and authentication.

## Authentication Endpoints

### 1. Register with Email Verification
**POST** `/api/register-with-email-verification`

### Request Body:
```json
{
    "email": "user@example.com",
    "verification_code": "1234",
    "name": "John Doe",
    "password": "password123",
    "password_confirmation": "password123",
    "phone": "+62812345678" // optional
}
```

### Response (Success):
```json
{
    "success": true,
    "message": "Registration successful",
    "token": "jwt-auth-token-here",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "user@example.com",
        "phone": "+62812345678",
        "profile_image_url": null,
        "created_at": "2025-01-01T10:00:00.000000Z",
        "updated_at": "2025-01-01T10:00:00.000000Z",
        "email_verified_at": "2025-01-01T10:00:00.000000Z"
    }
}
```

### 2. Login
**POST** `/api/login`

### Request Body:
```json
{
    "email": "user@example.com",
    "password": "password123"
}
```

### Response (Success):
```json
{
    "success": true,
    "message": "Login successful",
    "token": "jwt-auth-token-here",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "user@example.com",
        "phone": "+62812345678",
        "profile_image_url": null,
        "created_at": "2025-01-01T10:00:00.000000Z",
        "updated_at": "2025-01-01T10:00:00.000000Z",
        "email_verified_at": "2025-01-01T10:00:00.000000Z"
    }
}
```

### 3. Check if Email Exists
**POST** `/api/check-email`

### Request Body:
```json
{
    "email": "user@example.com"
}
```

### Response:
```json
{
    "exists": true
}
```

### 4. Get Current User
**GET** `/api/user`
**Headers:** `Authorization: Bearer {token}`

### Response:
```json
{
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "phone": "+62812345678",
    "profile_image_url": null,
    "created_at": "2025-01-01T10:00:00.000000Z",
    "updated_at": "2025-01-01T10:00:00.000000Z",
    "email_verified_at": "2025-01-01T10:00:00.000000Z"
}
```

### 5. Update Profile
**PUT** `/api/profile`
**Headers:** `Authorization: Bearer {token}`

### Request Body:
```json
{
    "name": "John Updated",
    "phone": "+62812345679",
    "profile_image_url": "https://example.com/avatar.jpg"
}
```

### 6. Logout
**POST** `/api/logout`
**Headers:** `Authorization: Bearer {token}`

### Response:
```json
{
    "message": "Successfully logged out"
}
```

## Email Verification Endpoints

### 7. Send Verification Email
**POST** `/api/send-verification-email`

### Request Body:
```json
{
    "email": "user@example.com",
    "verification_code": "1234"
}
```

### Response (Success):
```json
{
    "success": true,
    "message": "Verification email sent successfully"
}
```

### 8. Verify Email Code
**POST** `/api/verify-email-code`

### Request Body:
```json
{
    "email": "user@example.com",
    "verification_code": "1234"
}
```

### Response (Success):
```json
{
    "success": true,
    "message": "Email verified successfully"
}
```

---

## Laravel Implementation

### 1. Install Laravel Sanctum for API Authentication
```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

### 2. User Migration (update existing users table)
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('profile_image_url')->nullable()->after('phone');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'profile_image_url']);
        });
    }
}
```

### 3. Email Verification Migration
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailVerificationsTable extends Migration
{
    public function up()
    {
        Schema::create('email_verifications', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index();
            $table->string('verification_code');
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('email_verifications');
    }
}
```

### 4. User Model (app/Models/User.php)
```php
<?php

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
        'profile_image_url',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
```

### 5. EmailVerification Model
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class EmailVerification extends Model
{
    protected $fillable = [
        'email',
        'verification_code',
        'expires_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime'
    ];

    public function isExpired()
    {
        return $this->expires_at < Carbon::now();
    }
}
```

### 6. Authentication Controller
```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Models\EmailVerification;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function registerWithEmailVerification(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users',
            'verification_code' => 'required|string|size:4',
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20'
        ]);

        try {
            // Verify the email verification code
            $verification = EmailVerification::where('email', $request->email)
                ->where('verification_code', $request->verification_code)
                ->where('expires_at', '>', Carbon::now())
                ->first();

            if (!$verification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired verification code'
                ], 400);
            }

            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'email_verified_at' => Carbon::now()
            ]);

            // Delete verification record
            $verification->delete();

            // Create token
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Registration successful',
                'token' => $token,
                'user' => $user
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed'
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {
            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'token' => $token,
                'user' => $user
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials'
        ], 401);
    }

    public function checkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $exists = User::where('email', $request->email)->exists();

        return response()->json([
            'exists' => $exists
        ]);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|nullable|string|max:20',
            'profile_image_url' => 'sometimes|nullable|url'
        ]);

        $user = $request->user();
        $user->update($request->only(['name', 'phone', 'profile_image_url']));

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }
}
```

### 7. Email Verification Controller
```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\EmailVerification;
use Carbon\Carbon;

class EmailVerificationController extends Controller
{
    public function sendVerificationEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'verification_code' => 'required|string|size:4'
        ]);

        try {
            // Delete old verification codes for this email
            EmailVerification::where('email', $request->email)->delete();

            // Create new verification record
            EmailVerification::create([
                'email' => $request->email,
                'verification_code' => $request->verification_code,
                'expires_at' => Carbon::now()->addMinutes(10)
            ]);

            // Send email
            Mail::send('emails.verification', [
                'code' => $request->verification_code
            ], function ($message) use ($request) {
                $message->to($request->email)
                        ->subject('Email Verification Code - Nutrifarm');
            });

            return response()->json([
                'success' => true,
                'message' => 'Verification email sent successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send verification email'
            ], 500);
        }
    }

    public function verifyEmailCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'verification_code' => 'required|string|size:4'
        ]);

        try {
            $verification = EmailVerification::where('email', $request->email)
                ->where('verification_code', $request->verification_code)
                ->where('expires_at', '>', Carbon::now())
                ->first();

            if ($verification) {
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
                'message' => 'Verification failed'
            ], 500);
        }
    }
}
```

### 8. API Routes (routes/api.php)
```php
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmailVerificationController;

// Public routes
Route::post('/send-verification-email', [EmailVerificationController::class, 'sendVerificationEmail']);
Route::post('/verify-email-code', [EmailVerificationController::class, 'verifyEmailCode']);
Route::post('/register-with-email-verification', [AuthController::class, 'registerWithEmailVerification']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/check-email', [AuthController::class, 'checkEmail']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Your existing API routes (favorites, products, etc.)
    // ...
});
```

### 9. Email Template (resources/views/emails/verification.blade.php)
```html
<!DOCTYPE html>
<html>
<head>
    <title>Email Verification - Nutrifarm</title>
</head>
<body>
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="text-align: center; margin-bottom: 40px;">
            <h1 style="color: #1B5E20;">Nutrifarm</h1>
        </div>
        
        <h2 style="color: #333;">Email Verification</h2>
        
        <p>Thank you for registering with Nutrifarm. Please use the following verification code to complete your registration:</p>
        
        <div style="background-color: #f5f5f5; padding: 20px; text-align: center; margin: 20px 0; border-radius: 8px;">
            <h1 style="color: #1B5E20; font-size: 32px; margin: 0; letter-spacing: 5px;">{{ $code }}</h1>
        </div>
        
        <p>This code will expire in 10 minutes. If you didn't request this verification, please ignore this email.</p>
        
        <hr style="margin: 30px 0; border: none; border-top: 1px solid #eee;">
        
        <p style="font-size: 14px; color: #666;">
            Best regards,<br>
            The Nutrifarm Team
        </p>
    </div>
</body>
</html>
```

### 10. Configuration
Update your `.env` file:
```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nutrifarm
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="Nutrifarm"

# Sanctum
SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1
```

### 11. Update AuthService Base URL
In your Flutter `auth_service.dart`, update:
```dart
static const String _baseUrl = 'https://your-actual-domain.com/api';
```

## Testing the Complete Flow

1. **User Registration:**
   - Enter email → Get verification code
   - Enter code → Complete registration with name/password
   - Auto-login with JWT token

2. **User Login:**
   - Enter email/password → Get JWT token
   - Access protected routes

3. **Persistent Login:**
   - Token saved locally
   - Auto-login on app restart
   - Token validation

This creates a complete authentication system with email verification!
