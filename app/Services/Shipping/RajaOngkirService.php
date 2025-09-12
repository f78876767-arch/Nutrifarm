<?php

namespace App\Services\Shipping;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RajaOngkirService
{
    protected string $key;
    protected string $baseUrl;

    public function __construct(?string $key = null, ?string $baseUrl = null)
    {
        $this->key = $key ?: (string) config('services.rajaongkir.key');
        $this->baseUrl = rtrim($baseUrl ?: (string) config('services.rajaongkir.base_url'), '/');
    }

    protected function client()
    {
        return Http::withHeaders([
            'key' => $this->key,
            'Accept' => 'application/json',
        ])->baseUrl($this->baseUrl);
    }

    public function provinces(): array
    {
        $res = $this->client()->get('/province');
        return $this->handle($res, 'results');
    }

    public function cities(int $provinceId = null): array
    {
        $res = $this->client()->get('/city', array_filter([
            'province' => $provinceId,
        ]));
        return $this->handle($res, 'results');
    }

    public function subdistricts(int $cityId): array
    {
        // pro plan only; expose for future use
        $res = $this->client()->get('/subdistrict', ['city' => $cityId]);
        return $this->handle($res, 'results');
    }

    public function cost(array $params): array
    {
        // params: origin, destination, weight (grams), courier (e.g., jne|tiki|pos|sicepat|jnt)
        $payload = [
            'origin' => $params['origin'] ?? null,
            'originType' => $params['originType'] ?? null, // city|subdistrict (pro)
            'destination' => $params['destination'] ?? null,
            'destinationType' => $params['destinationType'] ?? null, // city|subdistrict (pro)
            'weight' => (int) ($params['weight'] ?? 0),
            'courier' => strtolower((string) ($params['courier'] ?? 'jne')),
        ];
        $res = $this->client()->asForm()->post('/cost', $payload);
        $data = $this->handle($res, 'results');
        // Normalize to simple array of services
        $services = [];
        foreach ($data as $carrier) {
            $code = $carrier['code'] ?? '';
            foreach ($carrier['costs'] ?? [] as $svc) {
                $services[] = [
                    'courier' => strtoupper($code),
                    'service' => $svc['service'] ?? '',
                    'description' => $svc['description'] ?? '',
                    'cost' => $svc['cost'][0]['value'] ?? null,
                    'etd' => $svc['cost'][0]['etd'] ?? null,
                    'note' => $svc['cost'][0]['note'] ?? null,
                    'raw' => $svc,
                ];
            }
        }
        return $services;
    }

    protected function handle($response, string $key)
    {
        if (!$response->ok()) {
            Log::warning('rajaongkir.http_error', ['status' => $response->status(), 'body' => $response->body()]);
            $response->throw();
        }
        $json = $response->json();
        $status = $json['rajaongkir']['status']['code'] ?? 0;
        if ($status !== 200) {
            Log::warning('rajaongkir.api_error', ['status' => $json['rajaongkir']['status'] ?? null]);
            throw new \RuntimeException('RajaOngkir error');
        }
        return $json['rajaongkir'][$key] ?? [];
    }
}
