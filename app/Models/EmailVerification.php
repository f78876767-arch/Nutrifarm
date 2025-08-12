<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailVerification extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'email',
        'verification_code',
        'expires_at',
        'verified_at',
        'is_verified'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime'
    ];    // Check if verification code is still valid (not expired)
    public function isExpired()
    {
        return $this->expires_at->isPast();
    }
    
    // Generate a new 4-digit verification code (matching Flutter requirements)
    public static function generateCode()
    {
        return str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    }
}
