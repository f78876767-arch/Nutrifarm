<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use App\Services\FcmService;

class OrderPaidNotification extends Notification
{
    use Queueable;

    public function __construct(public int $orderId, public string $invoiceNo, public float $amount)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'order_paid',
            'title' => 'Pembayaran Berhasil',
            'body' => 'Order ' . $this->invoiceNo . ' sebesar Rp' . number_format($this->amount, 0, ',', '.') . ' telah dibayar.',
            'order_id' => $this->orderId,
            'invoice_no' => $this->invoiceNo,
        ];
    }

    // Helper to send FCM alongside database notification
    public function sendPush($notifiable)
    {
        $tokens = method_exists($notifiable, 'fcmTokens') ? $notifiable->fcmTokens()->pluck('token')->all() : [];
        if (empty($tokens)) return;
        app(FcmService::class)->sendToTokens($tokens, [
            'title' => 'Pembayaran Berhasil',
            'body' => 'Order ' . $this->invoiceNo . ' telah dibayar.',
            'data' => [ 'order_id' => $this->orderId, 'invoice_no' => $this->invoiceNo ],
        ]);
    }
}
