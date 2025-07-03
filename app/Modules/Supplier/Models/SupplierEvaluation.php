<?php

namespace App\Modules\Supplier\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class SupplierEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'user_id',
        'evaluation_date',
        'quality_rating',
        'delivery_rating',
        'service_rating',
        'price_rating',
        'communication_rating',
        'overall_rating',
        'comments',
        'recommendations',
        'is_active',
        'evaluation_period_start',
        'evaluation_period_end',
    ];

    protected $casts = [
        'evaluation_date' => 'date',
        'evaluation_period_start' => 'date',
        'evaluation_period_end' => 'date',
        'quality_rating' => 'decimal:1',
        'delivery_rating' => 'decimal:1',
        'service_rating' => 'decimal:1',
        'price_rating' => 'decimal:1',
        'communication_rating' => 'decimal:1',
        'overall_rating' => 'decimal:1',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Accessors
    public function getOverallRatingColorAttribute(): string
    {
        if ($this->overall_rating >= 4.5) return 'success';
        if ($this->overall_rating >= 3.5) return 'info';
        if ($this->overall_rating >= 2.5) return 'warning';
        return 'danger';
    }

    // Methods
    public function calculateOverallRating(): void
    {
        $ratings = [
            $this->quality_rating,
            $this->delivery_rating,
            $this->service_rating,
            $this->price_rating,
            $this->communication_rating,
        ];

        $validRatings = array_filter($ratings, fn($rating) => $rating > 0);
        
        if (count($validRatings) > 0) {
            $this->overall_rating = array_sum($validRatings) / count($validRatings);
        }
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($evaluation) {
            $evaluation->calculateOverallRating();
        });

        static::saved(function ($evaluation) {
            $evaluation->supplier->updateRating();
        });
    }
}
