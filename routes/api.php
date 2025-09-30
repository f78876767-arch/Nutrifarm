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
use App\Http\Controllers\Api\Shipping\RajaOngkirController;
use App\Http\Controllers\Api\Shipping\AddressController;
use App\Http\Controllers\Api\Shipping\JntController;
use App\Http\Controllers\Api\ForgotPasswordController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ReviewController;

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

// Shipping: RajaOngkir public lookups and cost
Route::prefix('shipping/rajaongkir')->group(function () {
    Route::get('/provinces', [RajaOngkirController::class, 'provinces']);
    Route::get('/cities', [RajaOngkirController::class, 'cities']);
    Route::get('/subdistricts', [RajaOngkirController::class, 'subdistricts']);
    Route::post('/cost', [RajaOngkirController::class, 'cost']);
});

// J&T Shipping endpoints
Route::prefix('shipping/jnt')->group(function () {
    // Public: tariff inquiry and tracking
    Route::post('/tariff', [JntController::class, 'tariff']);
    Route::post('/track', [JntController::class, 'track']);
    Route::post('/track', [JntController::class, 'track']);
    Route::post('/order/create', [JntController::class, 'createOrder']);

    // Authenticated: cancel shipment orders
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/order/cancel', [JntController::class, 'cancelOrder']);
    });
});

// Auth routes for address and a convenience endpoint to compute cost from saved address
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/shipping/address', [AddressController::class, 'upsert']);
    Route::get('/shipping/address', [AddressController::class, 'me']);

    Route::post('/shipping/rajaongkir/cost/from-profile', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'weight' => 'required|integer|min:1',
            'couriers' => 'required|array|min:1',
            'couriers.*' => 'string',
        ]);
        $origin = (int) config('shipping.origin_city_id');
        if (!$origin) {
            return response()->json(['error' => 'Origin city not configured'], 422);
        }
        $user = $request->user();
        if (!$user->city_id) {
            return response()->json(['error' => 'User city not set'], 422);
        }
        $svc = app(\App\Services\Shipping\RajaOngkirService::class);
        $results = [];
        foreach ($request->couriers as $c) {
            try {
                $services = $svc->cost([
                    'origin' => $origin,
                    'destination' => (int) $user->city_id,
                    'weight' => (int) $request->weight,
                    'courier' => strtolower($c),
                    'originType' => config('shipping.origin_type', 'city'),
                    'destinationType' => 'city',
                ]);
                $results = array_merge($results, $services);
            } catch (\Throwable $e) {
                // continue other couriers
            }
        }
        // sort by cost asc
        usort($results, fn($a,$b) => ($a['cost'] ?? 0) <=> ($b['cost'] ?? 0));
    return response()->json($results);
    });
});

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
    Route::get('orders/{id}/invoice', [OrderController::class, 'invoiceDoc']);
    Route::get('orders/{id}/receipt', [OrderController::class, 'receiptDoc']);
    Route::get('orders/{orderId}/reviewable', [OrderController::class, 'reviewable']);

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

    // Reviews
    Route::post('/reviews/upsert', [ReviewController::class, 'upsert']);

    // Authenticated user
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // Enhanced user profile
    Route::get('/me', [AuthController::class, 'me']);
    
    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // Notifications
    Route::post('/notifications/register-token', [NotificationController::class, 'registerToken']);
    Route::post('/notifications/unregister-token', [NotificationController::class, 'unregisterToken']);
    Route::post('/notifications/test', [NotificationController::class, 'sendTest']);

    // New notification utilities
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);
    Route::get('/notifications/count', [NotificationController::class, 'count']);
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
Route::get('products-segmented', [ProductController::class, 'segmented']);
Route::get('products-featured', [ProductController::class, 'featured']);
Route::get('products-popular', [ProductController::class, 'popular']);

// Promotion endpoints (public)
Route::get('/promotions/discounts', [PromotionController::class, 'getActiveDiscounts']);
Route::get('/promotions/flash-sales', [PromotionController::class, 'getActiveFlashSales']);
Route::get('/promotions/products/{productId}', [PromotionController::class, 'getProductPromotions']);
Route::post('/promotions/calculate-cart', [PromotionController::class, 'calculateCartTotal']);

// Public auth password reset routes
Route::post('/auth/forgot-password', [ForgotPasswordController::class, 'send'])->middleware('throttle:10,10');
Route::post('/auth/reset-password', [ForgotPasswordController::class, 'reset'])->middleware('throttle:10,10');

// Public product reviews
Route::get('/products/{productId}/reviews', [ReviewController::class, 'productReviews']);

// Banner routes
Route::get('/banners', [App\Http\Controllers\Api\BannerController::class, 'index']);

// Admin banner routes (dengan middleware auth jika diperlukan)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/banners', [App\Http\Controllers\Api\BannerController::class, 'store']);
    Route::put('/banners/{banner}', [App\Http\Controllers\Api\BannerController::class, 'update']);
    Route::delete('/banners/{banner}', [App\Http\Controllers\Api\BannerController::class, 'destroy']);
});

