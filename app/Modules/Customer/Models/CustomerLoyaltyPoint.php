<?php

namespace App\Modules\Customer\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomerLoyaltyPoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'points',
        'transaction_type',
        'reference',
        'description',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    const TYPE_EARNED = 'earned';
    const TYPE_REDEEMED = 'redeemed';
    const TYPE_EXPIRED = 'expired';
    const TYPE_ADJUSTED = 'adjusted';

    // Relationships
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // Scopes
    public function scopeEarned($query)
    {
        return $query->where('transaction_type', self::TYPE_EARNED);
    }

    public function scopeRedeemed($query)
    {
        return $query->where('transaction_type', self::TYPE_REDEEMED);
    }

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    // Accessors
    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function getTypeColorAttribute(): string
    {
        return match($this->transaction_type) {
            self::TYPE_EARNED => 'success',
            self::TYPE_REDEEMED => 'primary',
            self::TYPE_EXPIRED => 'danger',
            self::TYPE_ADJUSTED => 'warning',
            default => 'secondary',
        };
    }
}
