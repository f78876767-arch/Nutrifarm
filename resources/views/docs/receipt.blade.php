<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt</title>
    <style>
        @page { margin: 24px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
        .brand { display:flex; justify-content:space-between; align-items:center; margin-bottom: 16px; }
        .brand-left { display:flex; align-items:center; gap:10px; }
        .logo { height:28px; }
        .brand .name { font-size:20px; font-weight:700; color: #065f46; }
        .brand .meta { text-align:right; font-size:12px; }
        .card { border:1px solid #e5e7eb; border-radius:6px; padding:12px; margin-bottom:12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th, td { border: 1px solid #e5e7eb; padding: 8px; text-align: left; }
        th { background: #f9fafb; font-weight:600; }
        .totals { width: 50%; margin-left:auto; border-collapse: collapse; }
        .totals td { border:1px solid #e5e7eb; padding:6px; }
        .totals tr:last-child td { font-weight:700; background:#f3f4f6; }
        .badge { display:inline-block; padding:2px 6px; border-radius:9999px; background:#ecfeff; color:#0369a1; font-size:11px; }
        .muted { color:#6b7280; }
        .mono { font-family: DejaVu Sans Mono, monospace; }
        .footer { margin-top:24px; text-align:center; color:#6b7280; font-size:11px; }
    </style>
</head>
<body>
    <div class="brand">
        <div class="brand-left">
            @if(file_exists(public_path('images/nutrifarm-logo.png')))
                <img src="{{ public_path('images/nutrifarm-logo.png') }}" class="logo" alt="Nutrifarm">
            @else
                <div class="name">Nutrifarm</div>
            @endif
        </div>
        <div class="meta">
            <div><strong>Receipt</strong></div>
            <div>Order: {{ $order->external_id ?? ('NF-'.$order->id) }}</div>
            <div>Paid: {{ optional($order->paid_at)->format('d M Y H:i') ?? '-' }}</div>
            @if($order->payment_status)
                <div>Status: <span class="badge">{{ strtoupper($order->payment_status) }}</span></div>
            @endif
        </div>
    </div>

    <div class="card">
        <div style="font-weight:600; margin-bottom:6px">Customer</div>
        <div>{{ $order->user->name ?? 'Guest' }}</div>
        <div class="muted">{{ $order->user->email ?? ($order->customer_email ?? '-') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:45%">Item</th>
                <th style="width:20%">SKU/Variant</th>
                <th style="width:10%">Qty</th>
                <th style="width:12.5%">Price</th>
                <th style="width:12.5%">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @php $subtotal = 0; @endphp
            @foreach($order->orderProducts as $item)
                @php $sub = ($item->price ?? 0) * ($item->quantity ?? 0); $subtotal += $sub; @endphp
                <tr>
                    <td>{{ $item->product->name ?? ('Product #'.$item->product_id) }}</td>
                    <td class="mono">{{ optional($item->variant)->sku ?? '-' }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>Rp {{ number_format((float)($item->price ?? 0), 0, ',', '.') }}</td>
                    <td>Rp {{ number_format((float)$sub, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @php
        $discount = (float)($order->discount_amount ?? 0);
        $shipping = (float)($order->shipping_amount ?? 0);
        $tax = (float)($order->tax_amount ?? 0);
        $grand = ($order->total ?? $order->total_amount) ?? ($subtotal - $discount + $shipping + $tax);
    @endphp

    <table class="totals" style="margin-top:10px;">
        <tr><td>Total Paid</td><td style="text-align:right;">Rp {{ number_format((float)$grand, 0, ',', '.') }}</td></tr>
    </table>

    <div class="footer">
        Terima kasih atas pembayaran Anda.
    </div>
</body>
</html>
