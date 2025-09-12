# XenditService Fix

## The Problem

Your Laravel backend is throwing an error because `Log::info()` expects the second parameter to be an array, but you're passing a `stdClass` object.

## Quick Fix

In your `/Users/kevin/Nutrifarm/app/Services/XenditService.php` file, find line 25 and change:

```php
// ❌ Wrong - if $data is stdClass
Log::info('Xendit request', $data);

// ✅ Correct - convert to array
Log::info('Xendit request', (array) $data);
```

## Complete Fixed XenditService Example

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class XenditService
{
    private $secretKey;
    private $baseUrl = 'https://api.xendit.co';

    public function __construct()
    {
        $this->secretKey = config('services.xendit.secret_key');
    }

    public function createInvoice($data)
    {
        try {
            // ✅ Ensure data is array for logging
            Log::info('Creating Xendit invoice', [
                'external_id' => $data['external_id'] ?? 'unknown',
                'amount' => $data['amount'] ?? 0,
                'payer_email' => $data['payer_email'] ?? 'unknown',
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode($this->secretKey . ':'),
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/v2/invoices', [
                'external_id' => $data['external_id'],
                'amount' => $data['amount'],
                'payer_email' => $data['payer_email'],
                'description' => $data['description'] ?? '',
                'currency' => 'IDR',
                'invoice_duration' => 86400, // 24 hours
            ]);

            if ($response->successful()) {
                $result = $response->json();
                Log::info('Xendit invoice created successfully', [
                    'invoice_id' => $result['id'] ?? 'unknown',
                    'external_id' => $data['external_id'],
                ]);
                return $result;
            } else {
                $error = $response->json();
                Log::error('Xendit invoice creation failed', [
                    'status' => $response->status(),
                    'error' => $error,
                    'external_id' => $data['external_id'],
                ]);
                throw new \Exception('Failed to create Xendit invoice: ' . ($error['message'] ?? 'Unknown error'));
            }

        } catch (\Exception $e) {
            Log::error('Xendit service error', [
                'message' => $e->getMessage(),
                'external_id' => $data['external_id'] ?? 'unknown',
            ]);
            throw $e;
        }
    }
}
```

## Alternative: Quick Fix in Controller

If you can't modify the XenditService right now, you can also fix it in your controller:

```php
// In your XenditPaymentController.php
public function create(Request $request)
{
    try {
        // Convert request to array before passing to service
        $requestData = $request->all();
        
        // ✅ Pass array instead of request object
        $result = $this->xenditService->createInvoice($requestData);
        
        return response()->json($result);
        
    } catch (\Exception $e) {
        Log::error('Xendit payment creation failed', [
            'message' => $e->getMessage(),
            'request_data' => $request->all(), // ✅ Convert to array
        ]);
        
        return response()->json([
            'message' => 'Payment creation failed',
            'error' => $e->getMessage()
        ], 500);
    }
}
```

## Summary

The key is to ensure that whenever you pass data to `Log::info()` or `Log::error()`, the second parameter must be an array, not a `stdClass` object or request object.

Change any instance of:
- `Log::info('message', $object)` 
- To: `Log::info('message', (array) $object)`

This should fix the 500 error you're seeing from the Xendit endpoint.
