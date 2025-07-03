<?php

namespace App\Modules\Compliance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Translatable\HasTranslations;
use App\Models\User;

class ComplianceViolation extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'compliance_item_id',
        'inspection_id',
        'violation_type',
        'title',
        'description',
        'severity',
        'detected_date',
        'reported_by_id',
        'assigned_to_id',
        'status',
        'resolution_date',
        'resolution_description',
        'corrective_actions',
        'preventive_actions',
        'cost_impact',
        'currency',
        'regulatory_response',
        'fine_amount',
        'fine_paid',
        'fine_due_date',
        'documents',
        'photos',
        'notes',
        'follow_up_required',
        'follow_up_date',
        'escalation_level',
        'external_reference',
        'meta_data',
    ];

    protected $casts = [
        'detected_date' => 'datetime',
        'resolution_date' => 'datetime',
        'fine_due_date' => 'date',
        'follow_up_date' => 'date',
        'cost_impact' => 'decimal:2',
        'fine_amount' => 'decimal:2',
        'fine_paid' => 'boolean',
        'follow_up_required' => 'boolean',
        'corrective_actions' => 'array',
        'preventive_actions' => 'array',
        'documents' => 'array',
        'photos' => 'array',
        'meta_data' => 'array',
    ];

    public $translatable = ['title', 'description', 'resolution_description', 'notes'];

    // Violation Types
    const TYPE_EXPIRY = 'expiry';
    const TYPE_DOCUMENTATION = 'documentation';
    const TYPE_INSPECTION_FAILURE = 'inspection_failure';
    const TYPE_REGULATORY_BREACH = 'regulatory_breach';
    const TYPE_SAFETY_VIOLATION = 'safety_violation';
    const TYPE_QUALITY_ISSUE = 'quality_issue';
    const TYPE_ENVIRONMENTAL = 'environmental';
    const TYPE_FINANCIAL = 'financial';
    const TYPE_PROCEDURAL = 'procedural';
    const TYPE_OTHER = 'other';

    // Severity Levels
    const SEVERITY_CRITICAL = 'critical';
    const SEVERITY_HIGH = 'high';
    const SEVERITY_MEDIUM = 'medium';
    const SEVERITY_LOW = 'low';

    // Status
    const STATUS_OPEN = 'open';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_RESOLVED = 'resolved';
    const STATUS_CLOSED = 'closed';
    const STATUS_ESCALATED = 'escalated';

    // Escalation Levels
    const ESCALATION_NONE = 'none';
    const ESCALATION_SUPERVISOR = 'supervisor';
    const ESCALATION_MANAGEMENT = 'management';
    const ESCALATION_EXECUTIVE = 'executive';
    const ESCALATION_REGULATORY = 'regulatory';

    // Relationships
    public function complianceItem()
    {
        return $this->belongsTo(ComplianceItem::class);
    }

    public function inspection()
    {
        return $this->belongsTo(Inspection::class);
    }

    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    // Scopes
    public function scopeOpen($query)
    {
        return $query->whereIn('status', [self::STATUS_OPEN, self::STATUS_IN_PROGRESS]);
    }

    public function scopeClosed($query)
    {
        return $query->whereIn('status', [self::STATUS_RESOLVED, self::STATUS_CLOSED]);
    }

    public function scopeBySeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }

    public function scopeCritical($query)
    {
        return $query->where('severity', self::SEVERITY_CRITICAL);
    }

    public function scopeOverdue($query)
    {
        return $query->where('follow_up_date', '<', now())
                     ->where('follow_up_required', true)
                     ->whereIn('status', [self::STATUS_OPEN, self::STATUS_IN_PROGRESS]);
    }

    // Accessors
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_OPEN => 'danger',
            self::STATUS_IN_PROGRESS => 'warning',
            self::STATUS_RESOLVED => 'success',
            self::STATUS_CLOSED => 'secondary',
            self::STATUS_ESCALATED => 'dark',
            default => 'light',
        };
    }

    public function getSeverityColorAttribute(): string
    {
        return match($this->severity) {
            self::SEVERITY_CRITICAL => 'danger',
            self::SEVERITY_HIGH => 'warning',
            self::SEVERITY_MEDIUM => 'info',
            self::SEVERITY_LOW => 'success',
            default => 'light',
        };
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->follow_up_required && 
               $this->follow_up_date && 
               $this->follow_up_date->isPast() &&
               in_array($this->status, [self::STATUS_OPEN, self::STATUS_IN_PROGRESS]);
    }

    public function getDaysOpenAttribute(): int
    {
        return $this->detected_date->diffInDays(now());
    }

    // Methods
    public function assign(User $user): void
    {
        $this->assigned_to_id = $user->id;
        $this->status = self::STATUS_IN_PROGRESS;
        $this->save();
    }

    public function resolve(array $data): void
    {
        $this->update([
            'status' => self::STATUS_RESOLVED,
            'resolution_date' => now(),
            'resolution_description' => $data['resolution_description'],
            'corrective_actions' => $data['corrective_actions'] ?? [],
            'preventive_actions' => $data['preventive_actions'] ?? [],
            'cost_impact' => $data['cost_impact'] ?? 0,
        ]);
    }

    public function close(string $notes = null): void
    {
        $this->status = self::STATUS_CLOSED;
        
        if ($notes) {
            $this->notes = $notes;
        }
        
        $this->save();
    }

    public function escalate(string $level, string $reason = null): void
    {
        $this->escalation_level = $level;
        $this->status = self::STATUS_ESCALATED;
        
        if ($reason) {
            $this->meta_data = array_merge($this->meta_data ?? [], [
                'escalation_reason' => $reason,
                'escalated_at' => now(),
            ]);
        }
        
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

    public function addPreventiveAction(string $action, \DateTime $dueDate = null): void
    {
        $actions = $this->preventive_actions ?? [];
        $actions[] = [
            'action' => $action,
            'due_date' => $dueDate,
            'status' => 'pending',
            'assigned_at' => now(),
        ];
        
        $this->preventive_actions = $actions;
        $this->save();
    }

    public function payFine(): void
    {
        $this->fine_paid = true;
        $this->save();
    }

    // Static Methods
    public static function getViolationTypes(): array
    {
        return [
            self::TYPE_EXPIRY => 'Expiry Violation',
            self::TYPE_DOCUMENTATION => 'Documentation Issue',
            self::TYPE_INSPECTION_FAILURE => 'Inspection Failure',
            self::TYPE_REGULATORY_BREACH => 'Regulatory Breach',
            self::TYPE_SAFETY_VIOLATION => 'Safety Violation',
            self::TYPE_QUALITY_ISSUE => 'Quality Issue',
            self::TYPE_ENVIRONMENTAL => 'Environmental Violation',
            self::TYPE_FINANCIAL => 'Financial Violation',
            self::TYPE_PROCEDURAL => 'Procedural Violation',
            self::TYPE_OTHER => 'Other',
        ];
    }

    public static function getSeverityLevels(): array
    {
        return [
            self::SEVERITY_CRITICAL => 'Critical',
            self::SEVERITY_HIGH => 'High',
            self::SEVERITY_MEDIUM => 'Medium',
            self::SEVERITY_LOW => 'Low',
        ];
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_OPEN => 'Open',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_RESOLVED => 'Resolved',
            self::STATUS_CLOSED => 'Closed',
            self::STATUS_ESCALATED => 'Escalated',
        ];
    }

    public static function getEscalationLevels(): array
    {
        return [
            self::ESCALATION_NONE => 'None',
            self::ESCALATION_SUPERVISOR => 'Supervisor',
            self::ESCALATION_MANAGEMENT => 'Management',
            self::ESCALATION_EXECUTIVE => 'Executive',
            self::ESCALATION_REGULATORY => 'Regulatory Authority',
        ];
    }

    public static function getCriticalViolations(): \Illuminate\Database\Eloquent\Collection
    {
        return self::critical()
            ->open()
            ->with(['complianceItem', 'assignedTo'])
            ->orderBy('detected_date')
            ->get();
    }

    public static function getOverdueViolations(): \Illuminate\Database\Eloquent\Collection
    {
        return self::overdue()
            ->with(['complianceItem', 'assignedTo'])
            ->orderBy('follow_up_date')
            ->get();
    }
}
