<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'status',
        'start_date',
        'end_date',
        'budget',
        'spent_budget',
        'target_audience',
        'campaign_data',
        'metrics',
        'created_by'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'budget' => 'decimal:2',
        'spent_budget' => 'decimal:2',
        'target_audience' => 'array',
        'campaign_data' => 'array',
        'metrics' => 'array'
    ];

    const TYPES = [
        'email' => 'Email Marketing',
        'banner' => 'Banner Campaign',
        'discount' => 'Discount Campaign',
        'flash_sale' => 'Flash Sale',
        'newsletter' => 'Newsletter',
        'social_media' => 'Social Media',
        'push_notification' => 'Push Notification'
    ];

    const STATUSES = [
        'draft' => 'Draft',
        'scheduled' => 'Scheduled',
        'active' => 'Active',
        'paused' => 'Paused',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTypeLabelAttribute()
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getStatusLabelAttribute()
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'draft' => 'text-gray-600 bg-gray-100',
            'scheduled' => 'text-blue-600 bg-blue-100',
            'active' => 'text-green-600 bg-green-100',
            'paused' => 'text-yellow-600 bg-yellow-100',
            'completed' => 'text-purple-600 bg-purple-100',
            'cancelled' => 'text-red-600 bg-red-100',
            default => 'text-gray-600 bg-gray-100'
        };
    }

    public function getProgressPercentageAttribute()
    {
        if ($this->budget <= 0) return 0;
        return min(100, ($this->spent_budget / $this->budget) * 100);
    }

    public function getRemainingBudgetAttribute()
    {
        return max(0, $this->budget - $this->spent_budget);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->where(function($q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate, $endDate])
              ->orWhereBetween('end_date', [$startDate, $endDate])
              ->orWhere(function($q2) use ($startDate, $endDate) {
                  $q2->where('start_date', '<=', $startDate)
                     ->where('end_date', '>=', $endDate);
              });
        });
    }
}
