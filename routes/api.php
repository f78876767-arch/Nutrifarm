

<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Http\Controllers\Api\XenditPaymentController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PhoneVerificationController;
use App\Http\Controllers\Api\EmailVerificationController;
use App\Http\Controllers\Api\PasswordlessAuthController;
use App\Http\Controllers\Api\PromotionController;
use App\Http\Controllers\Api\AuthController;

// TEST ROUTE - should appear in route:list
Route::get('/test-alive', function () {
    return response()->json(['status' => 'alive']);
});

// Xendit payment API routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/payments/xendit/create', [XenditPaymentController::class, 'createInvoice']);
    // ...other protected routes...
});
// Callback should remain public
Route::post('/payments/xendit/callback', [XenditPaymentController::class, 'handleCallback']);

// Protected API routes
Route::middleware('auth:sanctum')->group(function () {
    // Roles
    Route::get('roles', [RoleController::class, 'index']);
    Route::post('roles', [RoleController::class, 'store']);
    Route::post('roles/assign', [RoleController::class, 'assign']);
    Route::post('roles/remove', [RoleController::class, 'remove']);

    // Messages
    Route::get('messages', [MessageController::class, 'index']);
    Route::post('messages', [MessageController::class, 'store']);

    // Orders
    Route::apiResource('orders', OrderController::class);

    // Cart
    Route::get('cart', [CartController::class, 'index']);
    Route::post('cart', [CartController::class, 'store']);
    Route::put('cart/{id}', [CartController::class, 'update']);
    Route::delete('cart/{id}', [CartController::class, 'destroy']);
    Route::delete('cart', [CartController::class, 'clear']);
    Route::get('cart/count', [CartController::class, 'count']);

    // Favorites
    Route::get('favorites', [FavoriteController::class, 'index']);
    Route::post('favorites', [FavoriteController::class, 'store']);
    Route::delete('favorites/{id}', [FavoriteController::class, 'destroy']);
    Route::get('favorites/check/{productId}', [FavoriteController::class, 'check']);
    Route::post('favorites/toggle', [FavoriteController::class, 'toggle']);

    // Authenticated user
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // Enhanced user profile
    Route::get('/me', [AuthController::class, 'me']);
    
    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);
});


// Public registration and login
Route::post('/register', function (Request $request) {
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8',
    ]);

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
    ]);

    return response()->json($user, 201);
});

// Phone verification routes (public)
Route::post('/phone/send-code', [PhoneVerificationController::class, 'sendCode']);
Route::post('/phone/verify-code', [PhoneVerificationController::class, 'verifyCode']);
Route::post('/phone/check-verification', [PhoneVerificationController::class, 'checkVerification']);

// Email verification routes (public)
Route::post('/send-verification-email', [EmailVerificationController::class, 'sendVerificationEmail']);
Route::post('/verify-email-code', [EmailVerificationController::class, 'verifyEmailCode']);
Route::post('/email/generate-code', [EmailVerificationController::class, 'generateAndSendCode']);
Route::post('/email/check-status', [EmailVerificationController::class, 'checkVerificationStatus']);

// 3-Step Authentication Flow (public) - matching frontend team requirements
Route::post('/auth/check-email-availability', [AuthController::class, 'checkEmailAvailability']);
Route::post('/auth/send-verification-email', [AuthController::class, 'sendVerificationEmail']);
Route::post('/auth/verify-email-code', [AuthController::class, 'verifyEmailCode']);
Route::post('/auth/register-with-email-verification', [AuthController::class, 'registerWithEmailVerification']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::post('/login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    $token = $user->createToken('mobile')->plainTextToken;

    return response()->json(['token' => $token, 'user' => $user]);
});

// Public products
Route::apiResource('products', ProductController::class)->only(['index', 'show']);

// Promotion endpoints (public)
Route::get('/promotions/discounts', [PromotionController::class, 'getActiveDiscounts']);
Route::get('/promotions/flash-sales', [PromotionController::class, 'getActiveFlashSales']);
Route::get('/promotions/products/{productId}', [PromotionController::class, 'getProductPromotions']);
Route::post('/promotions/calculate-cart', [PromotionController::class, 'calculateCartTotal']);

