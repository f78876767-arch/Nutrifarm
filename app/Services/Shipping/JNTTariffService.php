<?php

namespace App\Services\Shipping;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class JNTTariffService
{
    public function __construct(
        private readonly string $baseUrl = '',
        private readonly string $tariffPath = '',
        private readonly string $customerName = '',
        private readonly string $key = ''
    ) {
        $cfg = config('services.jnt');
        $this->baseUrl = $cfg['base_url'] ?? '';
        $this->tariffPath = $cfg['paths']['tariff'] ?? '';
        // Use env-provided J&T dashboard customer name if set
        $this->customerName = env('JNT_CUSTOMER_NAME', $cfg['username'] ?? '');
        // Signing key from env/Dashboard
        $this->key = env('JNT_SIGN_KEY', '');
    }

    /**
     * Check tariff from origin (sendSiteCode) city to destination (destAreaCode) district.
     * Params must be uppercase as per J&T docs.
     * Weight expected as double (kg). If your weight is in grams, divide by 1000.
     */
    public function checkTariff(string $sendSiteCode, string $destAreaCode, float $weightKg): array
    {
        $payload = [
            'weight' => (string) $weightKg,
            'sendSiteCode' => strtoupper($sendSiteCode),
            'destAreaCode' => strtoupper($destAreaCode),
            'cusName' => $this->customerName,
            'productType' => 'EZ',
        ];

        $json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $signMode = strtolower((string) (config('services.jnt.sign_mode') ?? env('JNT_SIGN_MODE', 'hex')));
        if ($signMode === 'raw') {
            $signature = base64_encode(md5($json . $this->key, true));
        } else {
            $signature = base64_encode(md5($json . $this->key, false));
        }

        $url = rtrim($this->baseUrl, '/') . $this->tariffPath;

        $form = [
            'data' => $json,
            'sign' => $signature,
        ];

        $http = Http::asForm()->withHeaders([
            'Accept' => 'application/json, text/plain, */*',
            'Content-Type' => 'application/x-www-form-urlencoded',
        ]);

        // Optional SSL verify toggle for sandbox
        $verify = config('services.jnt.verify_ssl', true);
        if ($verify === false || strtolower((string) env('JNT_VERIFY_SSL', 'true')) === 'false') {
            $http = $http->withoutVerifying();
        }

        $resp = $http->post($url, $form);

        if (!$resp->ok()) {
            return [
                'is_success' => 'false',
                'message' => 'HTTP ' . $resp->status(),
                'raw' => $resp->body(),
            ];
        }

        $data = $resp->json();
        // If API returns JSON string in content, decode it for convenience
        if (isset($data['content']) && is_string($data['content'])) {
            $decoded = json_decode($data['content'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $data['content'] = $decoded;
            }
        }

        return $data;
    }
}
