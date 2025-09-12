<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    protected string $serverKey;

    public function __construct(?string $serverKey = null)
    {
        $this->serverKey = $serverKey ?: (string) env('FCM_SERVER_KEY');
    }

    public function sendToTokens(array $tokens, array $payload): array
    {
        if (empty($tokens)) return ['success' => 0];
        $body = [
            'registration_ids' => array_values($tokens),
            'notification' => [
                'title' => $payload['title'] ?? 'Nutrifarm',
                'body' => $payload['body'] ?? '',
            ],
            'data' => $payload['data'] ?? [],
            'android' => [ 'priority' => 'high' ],
            'apns' => [ 'headers' => [ 'apns-priority' => '10' ] ],
        ];
        $res = Http::withHeaders([
            'Authorization' => 'key=' . $this->serverKey,
            'Content-Type' => 'application/json',
        ])->post('https://fcm.googleapis.com/fcm/send', $body);
        if (!$res->ok()) {
            Log::warning('fcm.send_failed', ['status' => $res->status(), 'body' => $res->body()]);
        }
        return $res->json() ?? [];
    }
}
