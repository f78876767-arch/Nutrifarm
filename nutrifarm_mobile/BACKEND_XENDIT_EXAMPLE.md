# Backend Webhook Example (Laravel)

This is a reference implementation for handling Xendit webhooks in your Laravel backend.

## XenditPaymentController.php

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class XenditPaymentController extends Controller
{
    /**
     * Create Xendit Invoice
     */
    public function create(Request $request)
    {
        $request->validate([
            'amount' => 'required|integer|min:1',
            'external_id' => 'required|string|unique:orders,external_id',
            'payer_email' => 'required|email',
            'description' => 'nullable|string',
        ]);

        try {
            // Create order in database first
            $order = auth()->user()->orders()->create([
                'external_id' => $request->external_id,
                'amount' => $request->amount,
                'status' => 'pending',
                'description' => $request->description,
            ]);

            // Create Xendit invoice
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode(config('xendit.secret_key') . ':'),
                'Content-Type' => 'application/json',
            ])->post('https://api.xendit.co/v2/invoices', [
                'external_id' => $request->external_id,
                'amount' => $request->amount,
                'payer_email' => $request->payer_email,
                'description' => $request->description,
                'currency' => 'IDR',
                'invoice_duration' => 86400, // 24 hours
                'success_redirect_url' => config('app.frontend_url') . '/payment/success',
                'failure_redirect_url' => config('app.frontend_url') . '/payment/failed',
            ]);

            if ($response->successful()) {
                $invoiceData = $response->json();
                
                // Update order with invoice data
                $order->update([
                    'invoice_id' => $invoiceData['id'],
                    'invoice_url' => $invoiceData['invoice_url'],
                    'expiry_date' => $invoiceData['expiry_date'],
                ]);

                return response()->json($invoiceData);
            } else {
                $order->delete(); // Cleanup failed order
                return response()->json([
                    'message' => 'Failed to create Xendit invoice'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Xendit invoice creation failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Payment creation failed'
            ], 500);
        }
    }

    /**
     * Handle Xendit Webhook
     */
    public function callback(Request $request)
    {
        try {
            // Verify webhook authenticity (optional but recommended)
            $callbackToken = $request->header('X-CALLBACK-TOKEN');
            if ($callbackToken !== config('xendit.callback_token')) {
                Log::warning('Invalid Xendit callback token');
                return response()->json(['status' => 'error'], 401);
            }

            $data = $request->all();
            Log::info('Xendit webhook received:', $data);

            // Find order by external_id
            $order = Order::where('external_id', $data['external_id'])->first();
            
            if (!$order) {
                Log::warning('Order not found for external_id: ' . $data['external_id']);
                return response()->json(['status' => 'error'], 404);
            }

            // Handle different payment events
            switch ($data['status']) {
                case 'PAID':
                    $order->update([
                        'status' => 'paid',
                        'paid_at' => now(),
                        'payment_method' => $data['payment_method'] ?? null,
                        'payment_channel' => $data['payment_channel'] ?? null,
                    ]);

                    // Clear user's cart after successful payment
                    auth()->user()->cartItems()->delete();

                    // Send confirmation email/notification
                    // event(new OrderPaid($order));

                    Log::info("Order {$order->external_id} marked as paid");
                    break;

                case 'EXPIRED':
                    $order->update(['status' => 'expired']);
                    Log::info("Order {$order->external_id} expired");
                    break;

                case 'FAILED':
                    $order->update(['status' => 'failed']);
                    Log::info("Order {$order->external_id} failed");
                    break;
            }

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error('Xendit callback error: ' . $e->getMessage());
            return response()->json(['status' => 'error'], 500);
        }
    }
}
```

## Database Migration

```php
// Create orders table
Schema::create('orders', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('external_id')->unique();
    $table->string('invoice_id')->nullable();
    $table->integer('amount');
    $table->string('status')->default('pending'); // pending, paid, failed, expired
    $table->string('description')->nullable();
    $table->string('invoice_url')->nullable();
    $table->string('payment_method')->nullable();
    $table->string('payment_channel')->nullable();
    $table->timestamp('paid_at')->nullable();
    $table->timestamp('expiry_date')->nullable();
    $table->timestamps();
});
```

## Routes (api.php)

```php
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/payments/xendit/create', [XenditPaymentController::class, 'create']);
});

// Public webhook endpoint (no auth required)
Route::post('/payments/xendit/callback', [XenditPaymentController::class, 'callback']);
```

## Environment Variables (.env)

```env
XENDIT_SECRET_KEY=your_xendit_secret_key
XENDIT_CALLBACK_TOKEN=your_callback_verification_token
```

## Notes

- Make sure to configure the webhook URL in your Xendit dashboard
- Test with Xendit's sandbox environment first
- Implement proper error handling and logging
- Consider using Laravel queues for processing webhooks
- Add proper validation and security measures
