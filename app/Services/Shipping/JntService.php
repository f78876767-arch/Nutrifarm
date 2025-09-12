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

    public function __construct()
    {
        $cfg = config('services.jnt');
        $this->baseUrl = rtrim((string) ($cfg['base_url'] ?? ''), '/');
        $this->username = (string) ($cfg['username'] ?? '');
        $this->password = (string) ($cfg['password'] ?? '');
        $this->paths = $cfg['paths'] ?? [];
    $this->signKey = (string) (env('JNT_SIGN_KEY', $cfg['sign_key'] ?? ''));
    $this->customerName = (string) (env('JNT_CUSTOMER_NAME', $cfg['customer_name'] ?? ($this->username ?? '')));
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

    public function createOrder(array $payload)
    {
        $url = $this->endpoint('create_order');
        $res = Http::withHeaders($this->authHeader())
            ->asJson()
            ->post($url, $payload);
        if (!$res->successful()) {
            Log::warning('jnt.create_order_failed', ['status' => $res->status(), 'body' => $res->body()]);
        }
        return $res->json();
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
