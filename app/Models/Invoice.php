<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'xendit_id',
        'external_id',
        'user_id',
        'order_id',
        'status',
        'amount',
        'invoice_url',
        'payer_email',
        'description',
        'currency',
        'expiry_date',
        'raw',
    ];

    protected $casts = [
        'expiry_date' => 'datetime',
        'raw' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
