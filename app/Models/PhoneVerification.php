<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhoneVerification extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'phone_number',
        'verification_code',
        'is_verified',
        'expires_at'
    ];
    
    protected $casts = [
        'expires_at' => 'datetime',
        'is_verified' => 'boolean',
    ];
    
    // Check if verification code is still valid (not expired)
    public function isExpired()
    {
        return $this->expires_at->isPast();
    }
    
    // Generate a new 6-digit verification code
    public static function generateCode()
    {
        return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    }
}
