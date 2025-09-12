<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Services\XenditService;
use Illuminate\Support\Facades\Log;

class ReconcileXenditInvoices extends Command
{
    protected $signature = 'xendit:reconcile {--days=2 : Look back this many days for pending/expired orders}';

    protected $description = 'Reconcile orders with Xendit in case of missed webhooks (safe, idempotent).';

    public function handle(XenditService $xendit)
    {
        $days = (int) $this->option('days');
        $since = now()->subDays(max($days, 1));

        $orders = Order::query()
            ->whereNotNull('xendit_invoice_id')
            ->whereIn('payment_status', ['pending'])
            ->where('created_at', '>=', $since)
            ->limit(200)
            ->get();

        $this->info('Reconciling '. $orders->count() . ' orders since ' . $since->toDateTimeString());

        foreach ($orders as $order) {
            try {
                $inv = $xendit->getInvoice($order->xendit_invoice_id);
                if (!$inv) continue;
                $status = strtolower($inv['status'] ?? '');
                if ($status === 'paid' && $order->payment_status !== 'paid') {
                    Log::info('Reconcile: marking order paid', ['order_id' => $order->id]);
                    $order->update(['payment_status' => 'paid', 'status' => 'completed', 'paid_at' => now()]);
                    $order->histories()->create([
                        'status' => 'paid',
                        'note' => 'Reconciled with Xendit, webhook missed',
                        'metadata' => ['source' => 'reconcile'],
                    ]);
                } elseif (in_array($status, ['expired','failed','canceled'])) {
                    // Expired/failed invoices should not remain pending
                    $newPayment = $status === 'expired' ? 'expired' : 'failed';
                    Log::info('Reconcile: marking order not paid', ['order_id' => $order->id, 'status' => $status]);
                    $order->update(['payment_status' => $newPayment, 'status' => $status === 'expired' ? 'expired' : $order->status]);
                    $order->histories()->create([
                        'status' => $status === 'expired' ? 'expired' : 'failed',
                        'note' => 'Reconciled with Xendit ('.$status.')',
                        'metadata' => ['source' => 'reconcile'],
                    ]);
                }
            } catch (\Throwable $e) {
                Log::warning('Reconcile error', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            }
        }

        $this->info('Done.');
        return 0;
    }
}
