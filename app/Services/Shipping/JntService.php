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

    public function __construct()
    {
        $cfg = config('services.jnt');
        $this->baseUrl = rtrim((string) ($cfg['base_url'] ?? ''), '/');
        $this->username = (string) ($cfg['username'] ?? '');
        $this->password = (string) ($cfg['password'] ?? '');
        $this->paths = $cfg['paths'] ?? [];
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

    public function tariffInquiry(array $payload)
    {
        $url = $this->endpoint('tariff');
        $res = Http::withHeaders($this->authHeader())
            ->asForm()
            ->post($url, $payload);
        if (!$res->successful()) {
            Log::warning('jnt.tariff_inquiry_failed', ['status' => $res->status(), 'body' => $res->body()]);
        }
        return $res->json();
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
