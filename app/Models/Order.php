<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'total', 'status', 'resi', 'shipping_method', 'payment_method', 'payment_status',
        'external_id','xendit_invoice_id','xendit_invoice_url','invoice_no','cancel_reason','paid_at',
        'invoice_pdf_url','receipt_pdf_url', 'customer_email', 'subtotal_amount', 'tax_amount', 'shipping_amount', 'discount_amount', 'total_amount', 'notes'
    ];

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function (Order $order) {
            if (empty($order->invoice_no)) {
                $order->invoice_no = self::generateInvoiceNo();
            }
        });
    }

    public static function generateInvoiceNo(): string
    {
        $prefix = 'NUT-';
        $last = self::whereNotNull('invoice_no')
            ->where('invoice_no', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('invoice_no');

        $next = 1;
        if ($last) {
            $num = (int) preg_replace('/\D/', '', $last);
            $next = $num + 1;
        }
        return $prefix . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderProducts()
    {
        return $this->hasMany(OrderProduct::class);
    }

    // Add missing relation so webhook can record histories without crashing
    public function histories()
    {
        return $this->hasMany(OrderHistory::class);
    }

    // New: link to generated Xendit invoice record
    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function isReviewableBy(User $user = null): bool
    {
        $user = $user ?: $this->user;
        return $this->status === 'completed' && $user && $user->id === $this->user_id;
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
