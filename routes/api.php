

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
use App\Http\Controllers\Api\ProductController;

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

    // Authenticated user
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
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

