<?php

namespace App\Modules\Compliance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Translatable\HasTranslations;
use App\Models\User;

class Inspection extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'compliance_item_id',
        'inspection_type',
        'inspector_name',
        'inspector_organization',
        'inspector_contact',
        'scheduled_date',
        'actual_date',
        'duration_hours',
        'status',
        'result',
        'score',
        'findings',
        'recommendations',
        'corrective_actions',
        'follow_up_required',
        'follow_up_date',
        'documents',
        'photos',
        'notes',
        'cost',
        'currency',
        'certificate_issued',
        'certificate_number',
        'certificate_expiry',
        'next_inspection_date',
        'conducted_by_id',
        'approved_by_id',
        'meta_data',
    ];

    protected $casts = [
        'scheduled_date' => 'datetime',
        'actual_date' => 'datetime',
        'follow_up_date' => 'date',
        'certificate_expiry' => 'date',
        'next_inspection_date' => 'date',
        'duration_hours' => 'decimal:2',
        'score' => 'decimal:2',
        'cost' => 'decimal:2',
        'findings' => 'array',
        'recommendations' => 'array',
        'corrective_actions' => 'array',
        'documents' => 'array',
        'photos' => 'array',
        'follow_up_required' => 'boolean',
        'certificate_issued' => 'boolean',
        'meta_data' => 'array',
    ];

    public $translatable = ['notes'];

    // Inspection Types
    const TYPE_ROUTINE = 'routine';
    const TYPE_FOLLOW_UP = 'follow_up';
    const TYPE_COMPLAINT = 'complaint';
    const TYPE_RANDOM = 'random';
    const TYPE_RENEWAL = 'renewal';
    const TYPE_INITIAL = 'initial';
    const TYPE_SPECIAL = 'special';

    // Status
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_POSTPONED = 'postponed';

    // Results
    const RESULT_PASSED = 'passed';
    const RESULT_FAILED = 'failed';
    const RESULT_CONDITIONAL = 'conditional';
    const RESULT_PENDING = 'pending';

    // Relationships
    public function complianceItem()
    {
        return $this->belongsTo(ComplianceItem::class);
    }

    public function conductedBy()
    {
        return $this->belongsTo(User::class, 'conducted_by_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }

    // Scopes
    public function scopeScheduled($query)
    {
        return $query->where('status', self::STATUS_SCHEDULED);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeUpcoming($query, $days = 30)
    {
        return $query->where('scheduled_date', '>=', now())
                     ->where('scheduled_date', '<=', now()->addDays($days));
    }

    public function scopeOverdue($query)
    {
        return $query->where('scheduled_date', '<', now())
                     ->where('status', self::STATUS_SCHEDULED);
    }

    // Accessors
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_SCHEDULED => 'primary',
            self::STATUS_IN_PROGRESS => 'warning',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_CANCELLED => 'secondary',
            self::STATUS_POSTPONED => 'info',
            default => 'light',
        };
    }

    public function getResultColorAttribute(): string
    {
        return match($this->result) {
            self::RESULT_PASSED => 'success',
            self::RESULT_FAILED => 'danger',
            self::RESULT_CONDITIONAL => 'warning',
            self::RESULT_PENDING => 'info',
            default => 'light',
        };
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === self::STATUS_SCHEDULED && 
               $this->scheduled_date < now();
    }

    public function getIsUpcomingAttribute(): bool
    {
        return $this->status === self::STATUS_SCHEDULED && 
               $this->scheduled_date >= now() && 
               $this->scheduled_date <= now()->addDays(7);
    }

    // Methods
    public function start(): void
    {
        $this->status = self::STATUS_IN_PROGRESS;
        $this->actual_date = now();
        $this->save();
    }

    public function complete(array $data): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'result' => $data['result'],
            'score' => $data['score'] ?? null,
            'findings' => $data['findings'] ?? [],
            'recommendations' => $data['recommendations'] ?? [],
            'corrective_actions' => $data['corrective_actions'] ?? [],
            'follow_up_required' => $data['follow_up_required'] ?? false,
            'follow_up_date' => $data['follow_up_date'] ?? null,
            'certificate_issued' => $data['certificate_issued'] ?? false,
            'certificate_number' => $data['certificate_number'] ?? null,
            'certificate_expiry' => $data['certificate_expiry'] ?? null,
            'next_inspection_date' => $data['next_inspection_date'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        // Calculate duration if actual_date is set
        if ($this->actual_date) {
            $this->duration_hours = $this->actual_date->diffInHours(now());
            $this->save();
        }

        // Update compliance item if inspection failed
        if ($data['result'] === self::RESULT_FAILED) {
            $this->complianceItem->update(['status' => ComplianceItem::STATUS_SUSPENDED]);
            
            // Create violation
            $this->complianceItem->violations()->create([
                'violation_type' => ComplianceViolation::TYPE_INSPECTION_FAILURE,
                'description' => 'Failed inspection: ' . implode(', ', $data['findings'] ?? []),
                'severity' => ComplianceViolation::SEVERITY_HIGH,
                'detected_date' => now(),
                'status' => ComplianceViolation::STATUS_OPEN,
                'inspection_id' => $this->id,
            ]);
        }
    }

    public function cancel(string $reason = null): void
    {
        $this->status = self::STATUS_CANCELLED;
        
        if ($reason) {
            $this->meta_data = array_merge($this->meta_data ?? [], ['cancellation_reason' => $reason]);
        }
        
        $this->save();
    }

    public function postpone(\DateTime $newDate, string $reason = null): void
    {
        $oldDate = $this->scheduled_date;
        
        $this->scheduled_date = $newDate;
        $this->status = self::STATUS_POSTPONED;
        
        $this->meta_data = array_merge($this->meta_data ?? [], [
            'postponed_from' => $oldDate,
            'postpone_reason' => $reason,
            'postponed_at' => now(),
        ]);
        
        $this->save();
    }

    public function addFinding(string $finding, string $severity = 'medium'): void
    {
        $findings = $this->findings ?? [];
        $findings[] = [
            'finding' => $finding,
            'severity' => $severity,
            'recorded_at' => now(),
        ];
        
        $this->findings = $findings;
        $this->save();
    }

    public function addRecommendation(string $recommendation, string $priority = 'medium'): void
    {
        $recommendations = $this->recommendations ?? [];
        $recommendations[] = [
            'recommendation' => $recommendation,
            'priority' => $priority,
            'recorded_at' => now(),
        ];
        
        $this->recommendations = $recommendations;
        $this->save();
    }

    public function addCorrectiveAction(string $action, \DateTime $dueDate = null): void
    {
        $actions = $this->corrective_actions ?? [];
        $actions[] = [
            'action' => $action,
            'due_date' => $dueDate,
            'status' => 'pending',
            'assigned_at' => now(),
        ];
        
        $this->corrective_actions = $actions;
        $this->save();
    }

    // Static Methods
    public static function getInspectionTypes(): array
    {
        return [
            self::TYPE_ROUTINE => 'Routine Inspection',
            self::TYPE_FOLLOW_UP => 'Follow-up Inspection',
            self::TYPE_COMPLAINT => 'Complaint Investigation',
            self::TYPE_RANDOM => 'Random Inspection',
            self::TYPE_RENEWAL => 'Renewal Inspection',
            self::TYPE_INITIAL => 'Initial Inspection',
            self::TYPE_SPECIAL => 'Special Inspection',
        ];
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_SCHEDULED => 'Scheduled',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_POSTPONED => 'Postponed',
        ];
    }

    public static function getResults(): array
    {
        return [
            self::RESULT_PASSED => 'Passed',
            self::RESULT_FAILED => 'Failed',
            self::RESULT_CONDITIONAL => 'Conditional',
            self::RESULT_PENDING => 'Pending',
        ];
    }

    public static function getUpcomingInspections(int $days = 7): \Illuminate\Database\Eloquent\Collection
    {
        return self::upcoming($days)
            ->with(['complianceItem', 'conductedBy'])
            ->orderBy('scheduled_date')
            ->get();
    }

    public static function getOverdueInspections(): \Illuminate\Database\Eloquent\Collection
    {
        return self::overdue()
            ->with(['complianceItem', 'conductedBy'])
            ->orderBy('scheduled_date')
            ->get();
    }
}
