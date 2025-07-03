<?php

namespace App\Modules\Compliance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Models\User;

class ComplianceItem extends Model
{
    use HasFactory, SoftDeletes, HasTranslations, LogsActivity;

    protected $fillable = [
        'title',
        'description',
        'compliance_type',
        'category',
        'regulatory_body',
        'reference_number',
        'issue_date',
        'expiry_date',
        'renewal_date',
        'status',
        'priority',
        'responsible_person_id',
        'department',
        'cost',
        'currency',
        'documents',
        'requirements',
        'notes',
        'reminder_days',
        'auto_renewal',
        'compliance_score',
        'risk_level',
        'tags',
        'external_reference',
        'is_active',
        'meta_data',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'renewal_date' => 'date',
        'cost' => 'decimal:2',
        'documents' => 'array',
        'requirements' => 'array',
        'tags' => 'array',
        'auto_renewal' => 'boolean',
        'compliance_score' => 'decimal:2',
        'is_active' => 'boolean',
        'meta_data' => 'array',
    ];

    public $translatable = ['title', 'description', 'notes'];

    // Compliance Types
    const TYPE_LICENSE = 'license';
    const TYPE_PERMIT = 'permit';
    const TYPE_REGISTRATION = 'registration';
    const TYPE_CERTIFICATION = 'certification';
    const TYPE_INSPECTION = 'inspection';
    const TYPE_AUDIT = 'audit';
    const TYPE_TRAINING = 'training';
    const TYPE_INSURANCE = 'insurance';
    const TYPE_CONTRACT = 'contract';
    const TYPE_OTHER = 'other';

    // Status
    const STATUS_ACTIVE = 'active';
    const STATUS_EXPIRED = 'expired';
    const STATUS_PENDING = 'pending';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_UNDER_REVIEW = 'under_review';

    // Priority Levels
    const PRIORITY_CRITICAL = 'critical';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_LOW = 'low';

    // Risk Levels
    const RISK_VERY_HIGH = 'very_high';
    const RISK_HIGH = 'high';
    const RISK_MEDIUM = 'medium';
    const RISK_LOW = 'low';
    const RISK_VERY_LOW = 'very_low';

    // Categories
    const CATEGORY_BUSINESS = 'business';
    const CATEGORY_HEALTH = 'health';
    const CATEGORY_SAFETY = 'safety';
    const CATEGORY_ENVIRONMENTAL = 'environmental';
    const CATEGORY_FINANCIAL = 'financial';
    const CATEGORY_QUALITY = 'quality';
    const CATEGORY_SECURITY = 'security';
    const CATEGORY_PHARMACEUTICAL = 'pharmaceutical';

    // Relationships
    public function responsiblePerson()
    {
        return $this->belongsTo(User::class, 'responsible_person_id');
    }

    public function inspections()
    {
        return $this->hasMany(Inspection::class);
    }

    public function renewals()
    {
        return $this->hasMany(ComplianceRenewal::class);
    }

    public function violations()
    {
        return $this->hasMany(ComplianceViolation::class);
    }

    public function reminders()
    {
        return $this->hasMany(ComplianceReminder::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                     ->where('status', self::STATUS_ACTIVE);
    }

    public function scopeExpiring($query, $days = 30)
    {
        return $query->where('expiry_date', '<=', now()->addDays($days))
                     ->where('expiry_date', '>', now())
                     ->where('status', self::STATUS_ACTIVE);
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now())
                     ->where('status', '!=', self::STATUS_EXPIRED);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('compliance_type', $type);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeByRisk($query, $risk)
    {
        return $query->where('risk_level', $risk);
    }

    // Accessors
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_ACTIVE => 'success',
            self::STATUS_EXPIRED => 'danger',
            self::STATUS_PENDING => 'warning',
            self::STATUS_SUSPENDED => 'secondary',
            self::STATUS_CANCELLED => 'dark',
            self::STATUS_UNDER_REVIEW => 'info',
            default => 'light',
        };
    }

    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            self::PRIORITY_CRITICAL => 'danger',
            self::PRIORITY_HIGH => 'warning',
            self::PRIORITY_MEDIUM => 'info',
            self::PRIORITY_LOW => 'secondary',
            default => 'light',
        };
    }

    public function getRiskColorAttribute(): string
    {
        return match($this->risk_level) {
            self::RISK_VERY_HIGH => 'danger',
            self::RISK_HIGH => 'warning',
            self::RISK_MEDIUM => 'info',
            self::RISK_LOW => 'success',
            self::RISK_VERY_LOW => 'secondary',
            default => 'light',
        };
    }

    public function getDaysUntilExpiryAttribute(): ?int
    {
        return $this->expiry_date ? now()->diffInDays($this->expiry_date, false) : null;
    }

    public function getIsExpiringAttribute(): bool
    {
        return $this->days_until_expiry !== null && 
               $this->days_until_expiry <= ($this->reminder_days ?? 30) && 
               $this->days_until_expiry > 0;
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function getComplianceStatusAttribute(): string
    {
        if ($this->is_expired) {
            return 'expired';
        } elseif ($this->is_expiring) {
            return 'expiring';
        } elseif ($this->status === self::STATUS_ACTIVE) {
            return 'compliant';
        } else {
            return 'non_compliant';
        }
    }

    // Methods
    public function markExpired(): void
    {
        $this->status = self::STATUS_EXPIRED;
        $this->save();

        // Create violation record
        $this->violations()->create([
            'violation_type' => ComplianceViolation::TYPE_EXPIRY,
            'description' => "Compliance item expired on {$this->expiry_date->format('Y-m-d')}",
            'severity' => ComplianceViolation::SEVERITY_HIGH,
            'detected_date' => now(),
            'status' => ComplianceViolation::STATUS_OPEN,
        ]);
    }

    public function renew(array $data): ComplianceRenewal
    {
        $renewal = $this->renewals()->create([
            'previous_expiry_date' => $this->expiry_date,
            'new_expiry_date' => $data['new_expiry_date'],
            'renewal_date' => $data['renewal_date'] ?? now(),
            'cost' => $data['cost'] ?? 0,
            'currency' => $data['currency'] ?? 'IQD',
            'reference_number' => $data['reference_number'] ?? null,
            'notes' => $data['notes'] ?? null,
            'documents' => $data['documents'] ?? [],
            'status' => ComplianceRenewal::STATUS_COMPLETED,
        ]);

        // Update the compliance item
        $this->update([
            'expiry_date' => $data['new_expiry_date'],
            'renewal_date' => $data['renewal_date'] ?? now(),
            'status' => self::STATUS_ACTIVE,
            'reference_number' => $data['reference_number'] ?? $this->reference_number,
        ]);

        return $renewal;
    }

    public function createReminder(int $daysBefore = null): ComplianceReminder
    {
        $daysBefore = $daysBefore ?? $this->reminder_days ?? 30;
        $reminderDate = $this->expiry_date->subDays($daysBefore);

        return $this->reminders()->create([
            'reminder_date' => $reminderDate,
            'days_before_expiry' => $daysBefore,
            'message' => "Compliance item '{$this->title}' will expire in {$daysBefore} days",
            'status' => ComplianceReminder::STATUS_PENDING,
            'reminder_type' => ComplianceReminder::TYPE_EXPIRY,
        ]);
    }

    public function calculateComplianceScore(): float
    {
        $score = 100;

        // Deduct points for expired items
        if ($this->is_expired) {
            $score -= 50;
        } elseif ($this->is_expiring) {
            $score -= 20;
        }

        // Deduct points for violations
        $violationCount = $this->violations()->open()->count();
        $score -= ($violationCount * 10);

        // Deduct points for missing documents
        $requiredDocs = count($this->requirements ?? []);
        $providedDocs = count($this->documents ?? []);
        if ($requiredDocs > 0) {
            $docCompleteness = ($providedDocs / $requiredDocs) * 100;
            $score = ($score * $docCompleteness) / 100;
        }

        // Adjust for risk level
        $riskMultiplier = match($this->risk_level) {
            self::RISK_VERY_HIGH => 0.5,
            self::RISK_HIGH => 0.7,
            self::RISK_MEDIUM => 0.85,
            self::RISK_LOW => 0.95,
            self::RISK_VERY_LOW => 1.0,
            default => 0.8,
        };

        $score *= $riskMultiplier;

        return max(0, min(100, round($score, 2)));
    }

    public function updateComplianceScore(): void
    {
        $this->compliance_score = $this->calculateComplianceScore();
        $this->save();
    }

    public function getRequiredActions(): array
    {
        $actions = [];

        if ($this->is_expired) {
            $actions[] = [
                'type' => 'urgent',
                'action' => 'Renew expired compliance item',
                'priority' => 'critical',
                'due_date' => now(),
            ];
        } elseif ($this->is_expiring) {
            $actions[] = [
                'type' => 'warning',
                'action' => 'Prepare for renewal',
                'priority' => 'high',
                'due_date' => $this->expiry_date,
            ];
        }

        // Check for missing documents
        $requiredDocs = $this->requirements ?? [];
        $providedDocs = array_column($this->documents ?? [], 'type');
        $missingDocs = array_diff($requiredDocs, $providedDocs);

        foreach ($missingDocs as $doc) {
            $actions[] = [
                'type' => 'document',
                'action' => "Upload required document: {$doc}",
                'priority' => 'medium',
                'due_date' => $this->expiry_date,
            ];
        }

        // Check for open violations
        $openViolations = $this->violations()->open()->count();
        if ($openViolations > 0) {
            $actions[] = [
                'type' => 'violation',
                'action' => "Resolve {$openViolations} open violation(s)",
                'priority' => 'high',
                'due_date' => now()->addDays(7),
            ];
        }

        return $actions;
    }

    // Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'expiry_date', 'compliance_score', 'risk_level'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Static Methods
    public static function getComplianceTypes(): array
    {
        return [
            self::TYPE_LICENSE => 'License',
            self::TYPE_PERMIT => 'Permit',
            self::TYPE_REGISTRATION => 'Registration',
            self::TYPE_CERTIFICATION => 'Certification',
            self::TYPE_INSPECTION => 'Inspection',
            self::TYPE_AUDIT => 'Audit',
            self::TYPE_TRAINING => 'Training',
            self::TYPE_INSURANCE => 'Insurance',
            self::TYPE_CONTRACT => 'Contract',
            self::TYPE_OTHER => 'Other',
        ];
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_EXPIRED => 'Expired',
            self::STATUS_PENDING => 'Pending',
            self::STATUS_SUSPENDED => 'Suspended',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_UNDER_REVIEW => 'Under Review',
        ];
    }

    public static function getPriorities(): array
    {
        return [
            self::PRIORITY_CRITICAL => 'Critical',
            self::PRIORITY_HIGH => 'High',
            self::PRIORITY_MEDIUM => 'Medium',
            self::PRIORITY_LOW => 'Low',
        ];
    }

    public static function getRiskLevels(): array
    {
        return [
            self::RISK_VERY_HIGH => 'Very High',
            self::RISK_HIGH => 'High',
            self::RISK_MEDIUM => 'Medium',
            self::RISK_LOW => 'Low',
            self::RISK_VERY_LOW => 'Very Low',
        ];
    }

    public static function getCategories(): array
    {
        return [
            self::CATEGORY_BUSINESS => 'Business',
            self::CATEGORY_HEALTH => 'Health',
            self::CATEGORY_SAFETY => 'Safety',
            self::CATEGORY_ENVIRONMENTAL => 'Environmental',
            self::CATEGORY_FINANCIAL => 'Financial',
            self::CATEGORY_QUALITY => 'Quality',
            self::CATEGORY_SECURITY => 'Security',
            self::CATEGORY_PHARMACEUTICAL => 'Pharmaceutical',
        ];
    }

    public static function getExpiringItems(int $days = 30): \Illuminate\Database\Eloquent\Collection
    {
        return self::expiring($days)
            ->with('responsiblePerson')
            ->orderBy('expiry_date')
            ->get();
    }

    public static function getExpiredItems(): \Illuminate\Database\Eloquent\Collection
    {
        return self::expired()
            ->with('responsiblePerson')
            ->orderBy('expiry_date')
            ->get();
    }

    public static function getComplianceDashboard(): array
    {
        $total = self::count();
        $active = self::active()->count();
        $expired = self::expired()->count();
        $expiring = self::expiring(30)->count();
        $critical = self::where('priority', self::PRIORITY_CRITICAL)->count();

        return [
            'total_items' => $total,
            'active_items' => $active,
            'expired_items' => $expired,
            'expiring_items' => $expiring,
            'critical_items' => $critical,
            'compliance_rate' => $total > 0 ? ($active / $total) * 100 : 100,
        ];
    }
}
