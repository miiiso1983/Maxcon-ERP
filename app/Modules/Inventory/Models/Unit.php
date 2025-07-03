<?php

namespace App\Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Translatable\HasTranslations;

class Unit extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, HasTranslations;

    protected $fillable = [
        'name',
        'short_name',
        'description',
        'base_unit_id',
        'conversion_factor',
        'is_active',
        'type',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'conversion_factor' => 'decimal:4',
    ];

    public $translatable = ['name', 'short_name', 'description'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'short_name', 'base_unit_id', 'conversion_factor', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function baseUnit()
    {
        return $this->belongsTo(Unit::class, 'base_unit_id');
    }

    public function derivedUnits()
    {
        return $this->hasMany(Unit::class, 'base_unit_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeBaseUnits($query)
    {
        return $query->whereNull('base_unit_id');
    }

    // Methods
    public function isBaseUnit(): bool
    {
        return is_null($this->base_unit_id);
    }

    public function convertToBaseUnit(float $quantity): float
    {
        if ($this->isBaseUnit()) {
            return $quantity;
        }

        return $quantity * $this->conversion_factor;
    }

    public function convertFromBaseUnit(float $quantity): float
    {
        if ($this->isBaseUnit()) {
            return $quantity;
        }

        return $quantity / $this->conversion_factor;
    }

    public function convertTo(Unit $targetUnit, float $quantity): float
    {
        if ($this->id === $targetUnit->id) {
            return $quantity;
        }

        // Convert to base unit first
        $baseQuantity = $this->convertToBaseUnit($quantity);
        
        // Then convert to target unit
        return $targetUnit->convertFromBaseUnit($baseQuantity);
    }
}
