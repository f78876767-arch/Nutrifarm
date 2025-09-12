<?php

namespace App\Services;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentService
{
    public function generateInvoicePdf(Order $order): array
    {
        $order->load(['user','orderProducts.product','orderProducts.variant']);
        $pdf = Pdf::loadView('docs.invoice', ['order' => $order])->setPaper('A4');
        $fileName = 'invoices/'.date('Y/m/').Str::uuid().'.pdf';
        Storage::disk('public')->put($fileName, $pdf->output());
        return [
            'path' => $fileName,
            'url' => Storage::disk('public')->url($fileName),
        ];
    }

    public function generateReceiptPdf(Order $order): array
    {
        $order->load(['user','orderProducts.product','orderProducts.variant']);
        $pdf = Pdf::loadView('docs.receipt', ['order' => $order])->setPaper('A4');
        $fileName = 'receipts/'.date('Y/m/').Str::uuid().'.pdf';
        Storage::disk('public')->put($fileName, $pdf->output());
        return [
            'path' => $fileName,
            'url' => Storage::disk('public')->url($fileName),
        ];
    }
}
