<?php

namespace App\Services\Shipping;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class JntService
{
    protected string $baseUrl;
    protected string $username;
    protected string $password;
    protected array $paths;
    protected string $signKey;
    protected string $customerName;
    protected string $orderBaseUrl;
    protected string $orderKey;
    protected string $orderApiKey;

    public function __construct()
    {
        $cfg = config('services.jnt');
        $this->baseUrl = rtrim((string) ($cfg['base_url'] ?? ''), '/');
        $this->username = (string) ($cfg['username'] ?? '');
        $this->password = (string) ($cfg['password'] ?? '');
        $this->paths = $cfg['paths'] ?? [];
        $this->signKey = (string) (env('JNT_SIGN_KEY', $cfg['sign_key'] ?? ''));
        $this->customerName = (string) (env('JNT_CUSTOMER_NAME', $cfg['customer_name'] ?? ($this->username ?? '')));
        
        // Order API specific configuration
        $this->orderBaseUrl = rtrim((string) ($cfg['order_base_url'] ?? $this->baseUrl), '/');
        $this->orderKey = (string) ($cfg['order_key'] ?? '');
        $this->orderApiKey = (string) ($cfg['order_api_key'] ?? '');
    }

    protected function authHeader(): array
    {
        $token = base64_encode($this->username . ':' . $this->password);
        return ['Authorization' => 'Basic ' . $token];
    }

    protected function endpoint(string $key): string
    {
        $path = $this->paths[$key] ?? '';
        return $this->baseUrl . $path;
    }

    protected function orderEndpoint(string $key): string
    {
        $path = $this->paths[$key] ?? '';
        return $this->orderBaseUrl . $path;
    }

    public function createOrder(array $orderData)
    {
        try {
            // Build order data according to J&T API specification
            $orderDate = now()->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s');
            $pickupStart = now()->setTimezone('Asia/Jakarta')->format('Y-m-d 08:00:00');
            $pickupEnd = now()->setTimezone('Asia/Jakarta')->format('Y-m-d 18:00:00');
            
            // Extract first item for required fields
            $firstItem = $orderData['goods'][0] ?? [];
            $totalWeight = collect($orderData['goods'])->sum('weight');
            $totalQty = collect($orderData['goods'])->sum('qty');
            $totalValue = collect($orderData['goods'])->sum('value');
            
            $data = [
                'username' => $this->username,
                'api_key' => $this->orderApiKey,
                'orderid' => $orderData['order_no'],
                'shipper_name' => $orderData['shipper']['name'],
                'shipper_contact' => $orderData['shipper']['name'], // Same as shipper_name
                'shipper_phone' => $orderData['shipper']['phone'],
                'shipper_addr' => $orderData['shipper']['address'],
                'origin_code' => $orderData['shipper']['area'], // Should be 3-letter code like JKT
                'receiver_name' => $orderData['receiver']['name'],
                'receiver_phone' => $orderData['receiver']['phone'],
                'receiver_addr' => $orderData['receiver']['address'],
                'receiver_zip' => $orderData['receiver']['postcode'],
                'destination_code' => $orderData['receiver']['area'], // Should be 3-letter code
                'receiver_area' => $orderData['receiver']['area'] . '001', // Add district code
                'qty' => $totalQty,
                'weight' => $totalWeight,
                'goodsdesc' => $firstItem['name'] ?? 'General goods',
                'servicetype' => $orderData['service_type'] === 'EZ' ? 1 : 6, // 1=Pickup, 6=Drop off
                'insurance' => $orderData['insurance'] ?? 0,
                'orderdate' => $orderDate,
                'item_name' => $firstItem['name'] ?? 'General Item',
                'cod' => $orderData['cod'] ?? 0,
                'sendstarttime' => $pickupStart,
                'sendendtime' => $pickupEnd,
                'expresstype' => '1', // 1 = EZ (Regular)
                'goodsvalue' => $totalValue,
            ];

            // Create the data_param as required by J&T API
            $dataParam = json_encode(['detail' => [$data]]);
            
            // Generate signature: base64(md5(data_param + key))
            $signature = base64_encode(md5($dataParam . $this->orderKey));
            
            $payload = [
                'data_param' => $dataParam,
                'data_sign' => $signature
            ];

            Log::info('JNT Order Debug', [
                'data' => $data,
                'data_param' => $dataParam,
                'order_key' => $this->orderKey,
                'signature' => $signature,
                'url' => $this->orderBaseUrl . '/jts-idn-ecommerce-api/api/order/create'
            ]);

            $response = Http::withOptions(['verify' => false])
                ->timeout(30)
                ->asForm() // Use form data instead of JSON
                ->post($this->orderBaseUrl . '/jts-idn-ecommerce-api/api/order/create', $payload);

            Log::info('JNT Order Response', [
                'status' => $response->status(),
                'body' => $response->body(),
                'headers' => $response->headers()
            ]);

            if (!$response->successful()) {
                return [
                    'is_success' => 'false',
                    'message' => 'HTTP ' . $response->status(),
                    'content' => null,
                ];
            }

            $data = $response->json();
            return $data ?? ['is_success' => 'false', 'message' => 'Invalid response'];

        } catch (\Exception $e) {
            Log::error('JNT Order Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'is_success' => 'false',
                'message' => $e->getMessage(),
                'content' => null
            ];
        }
    }

    public function trackShipment(string $awb)
    {
        $endpoint = 'https://demo-general.inuat-jntexpress.id/jandt_track/track/trackAction!tracking.action';
        $username = 'NUTRIFARMOFFICIAL';
        $password = 'jhLHXag1M7kF';
        $body = json_encode([
            'awb' => $awb,
            'eccompanyid' => $username
        ]);

        try {
            $response = \Illuminate\Support\Facades\Http::withBasicAuth($username, $password)
                ->withOptions(['verify' => false]) // Disable SSL verification for demo
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->send('POST', $endpoint, [
                    'body' => $body
                ]);

            \Log::info('JNT Track Response', [
                'awb' => $awb,
                'status' => $response->status(),
                'body' => $response->body(),
                'headers' => $response->headers(),
            ]);

            if (!$response->successful()) {
                return [
                    'error_id' => (string) $response->status(),
                    'error_message' => 'HTTP ' . $response->status(),
                ];
            }

            return $response->json();
        } catch (\Exception $e) {
            \Log::error('JNT Track Error', [
                'awb' => $awb,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'error_id' => '500',
                'error_message' => $e->getMessage(),
            ];
        }
    }

    public function cancelOrder(array $payload)
    {
        $url = $this->endpoint('cancel_order');
        $res = Http::withHeaders($this->authHeader())
            ->asJson()
            ->post($url, $payload);
        if (!$res->successful()) {
            Log::warning('jnt.cancel_order_failed', ['status' => $res->status(), 'body' => $res->body()]);
        }
        return $res->json();
    }

    public function tariffInquiry(array $params)
    {
        // Debug logging
        Log::info('JNT tariffInquiry called', [
            'mock' => env('JNT_MOCK'),
            'sign_key' => $this->signKey ? 'set' : 'empty',
            'base_url' => $this->baseUrl,
            'customer_name' => $this->customerName,
        ]);

        // Accept boolean-like values from .env: true/false, 1/0, yes/no, on/off
        $mock = filter_var(env('JNT_MOCK', false), FILTER_VALIDATE_BOOLEAN);
        if ($mock) {
            $weight = isset($params['weight']) ? (float) $params['weight'] : 1.0;
            $sendSiteCode = strtoupper((string) ($params['sendSiteCode'] ?? ''));
            $destAreaCode = strtoupper((string) ($params['destAreaCode'] ?? ''));
            
            $payload = [
                'weight' => (string) $weight,
                'sendSiteCode' => $sendSiteCode,
                'destAreaCode' => $destAreaCode,
                'cusName' => $this->customerName,
                'productType' => 'EZ',
            ];

            // Mock tariff calculation based on weight and distance
            $baseFee = 8000; // Base fee IDR 8,000
            $weightFee = ceil($weight) * 2000; // IDR 2,000 per kg
            
            // Distance-based fee (mock calculation)
            $distanceFee = 0;
            $etd = '1-2';
            
            // Simple distance simulation based on area codes
            if (substr($sendSiteCode, 0, 3) === substr($destAreaCode, 0, 3)) {
                // Same city shipping (e.g., JKT01 to JKT02)
                $distanceFee = 2000;
                $etd = '1-2';
            } else {
                // Inter-city shipping
                $cityPairs = [
                    'JKT' => ['BDG' => 5000, 'SBY' => 12000, 'MDN' => 25000, 'DPS' => 18000],
                    'TNG' => ['MDN' => 28000, 'BDG' => 7000, 'SBY' => 15000],
                    'BDG' => ['JKT' => 5000, 'SBY' => 10000, 'MDN' => 22000],
                ];
                
                $origin = substr($sendSiteCode, 0, 3);
                $dest = substr($destAreaCode, 0, 3);
                
                if (isset($cityPairs[$origin][$dest])) {
                    $distanceFee = $cityPairs[$origin][$dest];
                    $etd = '2-3'; // Inter-city takes longer
                } elseif (isset($cityPairs[$dest][$origin])) {
                    $distanceFee = $cityPairs[$dest][$origin];
                    $etd = '2-3';
                } else {
                    $distanceFee = 15000; // Default inter-city fee
                    $etd = '3-4';
                }
            }
            
            $totalFee = $baseFee + $weightFee + $distanceFee;
            
            return [
                'is_success' => 'true',
                'message' => 'MOCK',
                'content' => [
                    'request' => $payload,
                    'tariff' => [
                        [
                            'service' => 'EZ',
                            'totalFee' => $totalFee,
                            'etd' => $etd,
                            'currency' => 'IDR',
                        ],
                    ],
                ],
            ];
        }

        // Expected input: sendSiteCode, destAreaCode, weight (kg). Use env customer name, productType EZ.
        $payload = [
            'weight' => isset($params['weight']) ? (string) (float) $params['weight'] : '1',
            'sendSiteCode' => strtoupper((string) ($params['sendSiteCode'] ?? '')),
            'destAreaCode' => strtoupper((string) ($params['destAreaCode'] ?? '')),
            'cusName' => $this->customerName,
            'productType' => 'EZ',
        ];

        $json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        // Configurable sign mode: 'hex' (base64 of md5 hex string) or 'raw' (base64 of raw md5 bytes)
        $signMode = strtolower((string) (config('services.jnt.sign_mode') ?? env('JNT_SIGN_MODE', 'hex')));
        if ($signMode === 'raw') {
            $signature = base64_encode(md5($json . $this->signKey, true));
        } else {
            // default: hex
            $signature = base64_encode(md5($json . $this->signKey, false));
        }

        $form = [
            'data' => $json,
            'sign' => $signature,
        ];

        $url = $this->endpoint('tariff');
        
        Log::info('JNT Signature Debug', [
            'payload' => $payload,
            'json' => $json,
            'sign_key' => $this->signKey,
            'sign_mode' => $signMode,
            'signature' => $signature,
            'url' => $url,
        ]);
        $headers = [
            'Accept' => 'application/json, text/plain, */*',
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];
        // Do not attach Basic Auth for tariff: docs show Authentication Not Required
        $http = Http::asForm()->withHeaders($headers);

        // Allow disabling SSL verification for sandbox/dev endpoints with self-signed chains
        $verify = config('services.jnt.verify_ssl', true);
        if ($verify === false || strtolower((string) env('JNT_VERIFY_SSL', 'true')) === 'false') {
            $http = $http->withoutVerifying();
        }

        $res = $http->post($url, $form);

        Log::info('JNT API Response', [
            'status' => $res->status(),
            'body' => $res->body(),
            'headers' => $res->headers(),
        ]);

        if (!$res->successful()) {
            Log::warning('jnt.tariff_inquiry_failed', ['status' => $res->status(), 'body' => $res->body()]);
            return [
                'is_success' => 'false',
                'message' => 'HTTP ' . $res->status(),
                'content' => null,
            ];
        }

        $data = $res->json();
        if (isset($data['content']) && is_string($data['content'])) {
            $decoded = json_decode($data['content'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $data['content'] = $decoded;
            }
        }
        return $data;
    }

    public function track(array $payload)
    {
        $url = $this->endpoint('track');
        $res = Http::withHeaders($this->authHeader())
            ->asForm()
            ->post($url, $payload);
        if (!$res->successful()) {
            Log::warning('jnt.track_failed', ['status' => $res->status(), 'body' => $res->body()]);
        }
        return $res->json();
    }
}
