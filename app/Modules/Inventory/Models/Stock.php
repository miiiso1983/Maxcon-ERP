<?php

namespace App\Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Stock extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'quantity',
        'available_quantity',
        'reserved_quantity',
        'damaged_quantity',
        'batch_number',
        'expiry_date',
        'cost_price',
        'location',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'available_quantity' => 'decimal:2',
        'reserved_quantity' => 'decimal:2',
        'damaged_quantity' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'expiry_date' => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['product_id', 'warehouse_id', 'quantity', 'available_quantity', 'batch_number', 'expiry_date'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('available_quantity', '>', 0);
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now());
    }

    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->where('expiry_date', '<=', now()->addDays($days))
            ->where('expiry_date', '>', now());
    }

    public function scopeByBatch($query, string $batchNumber)
    {
        return $query->where('batch_number', $batchNumber);
    }

    // Accessors & Mutators
    public function getTotalValueAttribute(): float
    {
        return $this->quantity * $this->cost_price;
    }

    public function getAvailableValueAttribute(): float
    {
        return $this->available_quantity * $this->cost_price;
    }

    public function getDaysToExpiryAttribute(): ?int
    {
        if (!$this->expiry_date) {
            return null;
        }

        return now()->diffInDays($this->expiry_date, false);
    }

    // Methods
    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function isExpiringSoon(int $days = 30): bool
    {
        if (!$this->expiry_date) {
            return false;
        }

        return $this->expiry_date->isBefore(now()->addDays($days)) && !$this->isExpired();
    }

    public function reserve(float $quantity): bool
    {
        if ($this->available_quantity < $quantity) {
            return false;
        }

        $this->available_quantity -= $quantity;
        $this->reserved_quantity += $quantity;
        
        return $this->save();
    }

    public function unreserve(float $quantity): bool
    {
        if ($this->reserved_quantity < $quantity) {
            return false;
        }

        $this->reserved_quantity -= $quantity;
        $this->available_quantity += $quantity;
        
        return $this->save();
    }

    public function consume(float $quantity): bool
    {
        if ($this->available_quantity < $quantity) {
            return false;
        }

        $this->quantity -= $quantity;
        $this->available_quantity -= $quantity;
        
        return $this->save();
    }

    public function consumeReserved(float $quantity): bool
    {
        if ($this->reserved_quantity < $quantity) {
            return false;
        }

        $this->quantity -= $quantity;
        $this->reserved_quantity -= $quantity;
        
        return $this->save();
    }

    public function markAsDamaged(float $quantity, ?string $reason = null): bool
    {
        if ($this->available_quantity < $quantity) {
            return false;
        }

        $this->available_quantity -= $quantity;
        $this->damaged_quantity += $quantity;
        
        if ($reason) {
            $this->notes = ($this->notes ? $this->notes . "\n" : '') . "Damaged: {$reason}";
        }
        
        return $this->save();
    }

    public function adjust(float $newQuantity, ?string $reason = null): bool
    {
        $oldQuantity = $this->quantity;
        $difference = $newQuantity - $oldQuantity;
        
        $this->quantity = $newQuantity;
        $this->available_quantity += $difference;
        
        if ($reason) {
            $this->notes = ($this->notes ? $this->notes . "\n" : '') . "Adjusted: {$reason}";
        }
        
        return $this->save();
    }

    public function getStatusAttribute(): string
    {
        if ($this->isExpired()) {
            return 'expired';
        } elseif ($this->isExpiringSoon()) {
            return 'expiring_soon';
        } elseif ($this->available_quantity <= 0) {
            return 'out_of_stock';
        } elseif ($this->damaged_quantity > 0) {
            return 'has_damage';
        }

        return 'good';
    }
}
