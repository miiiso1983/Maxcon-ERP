<?php

namespace App\Modules\MedicalReps\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Modules\Customer\Models\Customer;
use Carbon\Carbon;

class CustomerVisit extends Model
{
    use HasFactory;

    protected $fillable = [
        'medical_rep_id',
        'customer_id',
        'visit_date',
        'visit_time',
        'visit_type',
        'purpose',
        'status',
        'check_in_time',
        'check_out_time',
        'check_in_location',
        'check_out_location',
        'distance_traveled',
        'duration_minutes',
        'notes',
        'outcomes',
        'next_visit_date',
        'products_discussed',
        'samples_given',
        'orders_taken',
        'feedback_received',
        'competitor_info',
        'photos',
        'documents',
        'gps_coordinates',
        'weather_conditions',
        'is_planned',
        'planned_by',
        'approved_by',
        'meta_data',
    ];

    protected $casts = [
        'visit_date' => 'date',
        'visit_time' => 'datetime',
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'next_visit_date' => 'date',
        'check_in_location' => 'array',
        'check_out_location' => 'array',
        'distance_traveled' => 'decimal:2',
        'products_discussed' => 'array',
        'samples_given' => 'array',
        'orders_taken' => 'array',
        'feedback_received' => 'array',
        'competitor_info' => 'array',
        'photos' => 'array',
        'documents' => 'array',
        'gps_coordinates' => 'array',
        'is_planned' => 'boolean',
        'meta_data' => 'array',
    ];

    // Visit Types
    const TYPE_ROUTINE = 'routine';
    const TYPE_FOLLOW_UP = 'follow_up';
    const TYPE_INTRODUCTION = 'introduction';
    const TYPE_PRODUCT_LAUNCH = 'product_launch';
    const TYPE_COMPLAINT = 'complaint';
    const TYPE_COLLECTION = 'collection';
    const TYPE_EMERGENCY = 'emergency';

    // Visit Status
    const STATUS_PLANNED = 'planned';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_MISSED = 'missed';
    const STATUS_RESCHEDULED = 'rescheduled';

    // Visit Purposes
    const PURPOSE_SALES = 'sales';
    const PURPOSE_RELATIONSHIP = 'relationship';
    const PURPOSE_EDUCATION = 'education';
    const PURPOSE_SUPPORT = 'support';
    const PURPOSE_FEEDBACK = 'feedback';
    const PURPOSE_COLLECTION = 'collection';

    // Relationships
    public function medicalRep()
    {
        return $this->belongsTo(MedicalRep::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function plannedBy()
    {
        return $this->belongsTo(MedicalRep::class, 'planned_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(MedicalRep::class, 'approved_by');
    }

    // Scopes
    public function scopePlanned($query)
    {
        return $query->where('status', self::STATUS_PLANNED);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeToday($query)
    {
        return $query->where('visit_date', now()->format('Y-m-d'));
    }

    public function scopeUpcoming($query, $days = 7)
    {
        return $query->where('visit_date', '>=', now())
                     ->where('visit_date', '<=', now()->addDays($days));
    }

    public function scopeByType($query, $type)
    {
        return $query->where('visit_type', $type);
    }

    public function scopeByPurpose($query, $purpose)
    {
        return $query->where('purpose', $purpose);
    }

    // Accessors
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PLANNED => 'primary',
            self::STATUS_IN_PROGRESS => 'warning',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_CANCELLED => 'secondary',
            self::STATUS_MISSED => 'danger',
            self::STATUS_RESCHEDULED => 'info',
            default => 'light',
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match($this->visit_type) {
            self::TYPE_ROUTINE => 'primary',
            self::TYPE_FOLLOW_UP => 'info',
            self::TYPE_INTRODUCTION => 'success',
            self::TYPE_PRODUCT_LAUNCH => 'warning',
            self::TYPE_COMPLAINT => 'danger',
            self::TYPE_COLLECTION => 'secondary',
            self::TYPE_EMERGENCY => 'dark',
            default => 'light',
        };
    }

    public function getActualDurationAttribute(): ?int
    {
        if ($this->check_in_time && $this->check_out_time) {
            return $this->check_in_time->diffInMinutes($this->check_out_time);
        }
        return null;
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === self::STATUS_PLANNED && 
               $this->visit_date < now()->format('Y-m-d');
    }

    public function getIsTodayAttribute(): bool
    {
        return $this->visit_date->isToday();
    }

    // Methods
    public function checkIn(array $location = null, array $metadata = []): void
    {
        $this->check_in_time = now();
        $this->check_in_location = $location;
        $this->status = self::STATUS_IN_PROGRESS;
        
        if (!empty($metadata)) {
            $this->meta_data = array_merge($this->meta_data ?? [], $metadata);
        }
        
        $this->save();
    }

    public function checkOut(array $location = null, array $metadata = []): void
    {
        $this->check_out_time = now();
        $this->check_out_location = $location;
        $this->status = self::STATUS_COMPLETED;
        
        // Calculate duration
        if ($this->check_in_time) {
            $this->duration_minutes = $this->check_in_time->diffInMinutes($this->check_out_time);
        }
        
        // Calculate distance if both locations are available
        if ($this->check_in_location && $location) {
            $this->distance_traveled = $this->calculateDistance(
                $this->check_in_location,
                $location
            );
        }
        
        if (!empty($metadata)) {
            $this->meta_data = array_merge($this->meta_data ?? [], $metadata);
        }
        
        $this->save();
    }

    public function cancel(string $reason = null): void
    {
        $this->status = self::STATUS_CANCELLED;
        
        if ($reason) {
            $this->meta_data = array_merge($this->meta_data ?? [], ['cancellation_reason' => $reason]);
        }
        
        $this->save();
    }

    public function reschedule(Carbon $newDate, string $newTime = null, string $reason = null): void
    {
        $oldDate = $this->visit_date;
        $oldTime = $this->visit_time;
        
        $this->visit_date = $newDate;
        if ($newTime) {
            $this->visit_time = Carbon::parse($newDate->format('Y-m-d') . ' ' . $newTime);
        }
        $this->status = self::STATUS_RESCHEDULED;
        
        $this->meta_data = array_merge($this->meta_data ?? [], [
            'rescheduled_from' => [
                'date' => $oldDate,
                'time' => $oldTime,
                'reason' => $reason,
                'rescheduled_at' => now(),
            ]
        ]);
        
        $this->save();
    }

    public function markMissed(string $reason = null): void
    {
        $this->status = self::STATUS_MISSED;
        
        if ($reason) {
            $this->meta_data = array_merge($this->meta_data ?? [], ['missed_reason' => $reason]);
        }
        
        $this->save();
    }

    public function addOutcome(string $outcome, array $details = []): void
    {
        $outcomes = $this->outcomes ?? [];
        $outcomes[] = [
            'outcome' => $outcome,
            'details' => $details,
            'recorded_at' => now(),
        ];
        
        $this->outcomes = $outcomes;
        $this->save();
    }

    public function addProductDiscussion(string $productName, array $details = []): void
    {
        $products = $this->products_discussed ?? [];
        $products[] = [
            'product' => $productName,
            'details' => $details,
            'discussed_at' => now(),
        ];
        
        $this->products_discussed = $products;
        $this->save();
    }

    public function giveSample(string $productName, int $quantity, array $details = []): void
    {
        $samples = $this->samples_given ?? [];
        $samples[] = [
            'product' => $productName,
            'quantity' => $quantity,
            'details' => $details,
            'given_at' => now(),
        ];
        
        $this->samples_given = $samples;
        $this->save();
    }

    public function recordOrder(array $orderDetails): void
    {
        $orders = $this->orders_taken ?? [];
        $orders[] = array_merge($orderDetails, [
            'recorded_at' => now(),
        ]);
        
        $this->orders_taken = $orders;
        $this->save();
    }

    public function addFeedback(string $feedback, string $category = 'general'): void
    {
        $feedbacks = $this->feedback_received ?? [];
        $feedbacks[] = [
            'feedback' => $feedback,
            'category' => $category,
            'received_at' => now(),
        ];
        
        $this->feedback_received = $feedbacks;
        $this->save();
    }

    public function recordCompetitorInfo(string $competitor, array $info): void
    {
        $competitors = $this->competitor_info ?? [];
        $competitors[] = [
            'competitor' => $competitor,
            'info' => $info,
            'recorded_at' => now(),
        ];
        
        $this->competitor_info = $competitors;
        $this->save();
    }

    private function calculateDistance(array $location1, array $location2): float
    {
        if (!isset($location1['lat'], $location1['lng'], $location2['lat'], $location2['lng'])) {
            return 0;
        }

        $lat1 = deg2rad($location1['lat']);
        $lon1 = deg2rad($location1['lng']);
        $lat2 = deg2rad($location2['lat']);
        $lon2 = deg2rad($location2['lng']);

        $deltaLat = $lat2 - $lat1;
        $deltaLon = $lon2 - $lon1;

        $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
             cos($lat1) * cos($lat2) *
             sin($deltaLon / 2) * sin($deltaLon / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = 6371 * $c; // Earth's radius in kilometers

        return round($distance, 2);
    }

    // Static Methods
    public static function getVisitTypes(): array
    {
        return [
            self::TYPE_ROUTINE => 'Routine Visit',
            self::TYPE_FOLLOW_UP => 'Follow-up',
            self::TYPE_INTRODUCTION => 'Introduction',
            self::TYPE_PRODUCT_LAUNCH => 'Product Launch',
            self::TYPE_COMPLAINT => 'Complaint Resolution',
            self::TYPE_COLLECTION => 'Collection',
            self::TYPE_EMERGENCY => 'Emergency',
        ];
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PLANNED => 'Planned',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_MISSED => 'Missed',
            self::STATUS_RESCHEDULED => 'Rescheduled',
        ];
    }

    public static function getPurposes(): array
    {
        return [
            self::PURPOSE_SALES => 'Sales',
            self::PURPOSE_RELATIONSHIP => 'Relationship Building',
            self::PURPOSE_EDUCATION => 'Education',
            self::PURPOSE_SUPPORT => 'Support',
            self::PURPOSE_FEEDBACK => 'Feedback Collection',
            self::PURPOSE_COLLECTION => 'Payment Collection',
        ];
    }

    public static function getTodaysVisits(MedicalRep $rep): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('medical_rep_id', $rep->id)
            ->today()
            ->with('customer')
            ->orderBy('visit_time')
            ->get();
    }

    public static function getOverdueVisits(MedicalRep $rep = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = self::where('status', self::STATUS_PLANNED)
            ->where('visit_date', '<', now()->format('Y-m-d'))
            ->with(['customer', 'medicalRep.employee']);

        if ($rep) {
            $query->where('medical_rep_id', $rep->id);
        }

        return $query->orderBy('visit_date')->get();
    }
}
