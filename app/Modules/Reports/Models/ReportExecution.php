<?php

namespace App\Modules\Reports\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class ReportExecution extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'parameters',
        'status',
        'started_at',
        'completed_at',
        'executed_by',
        'result_data',
        'row_count',
        'execution_time',
        'error_message',
        'export_format',
        'export_path',
    ];

    protected $casts = [
        'parameters' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'result_data' => 'array',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_RUNNING = 'running';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';

    // Relationships
    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    public function executedBy()
    {
        return $this->belongsTo(User::class, 'executed_by');
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('started_at', 'desc');
    }

    // Accessors
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'secondary',
            self::STATUS_RUNNING => 'info',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_FAILED => 'danger',
            self::STATUS_CANCELLED => 'warning',
            default => 'light',
        };
    }

    public function getExecutionTimeAttribute(): ?int
    {
        if (!$this->started_at || !$this->completed_at) {
            return null;
        }

        return $this->completed_at->diffInSeconds($this->started_at);
    }

    public function getDurationTextAttribute(): string
    {
        $executionTime = $this->execution_time;
        
        if (!$executionTime) {
            return 'N/A';
        }

        if ($executionTime < 60) {
            return "{$executionTime}s";
        }

        $minutes = floor($executionTime / 60);
        $seconds = $executionTime % 60;
        
        return "{$minutes}m {$seconds}s";
    }

    // Methods
    public function cancel(): bool
    {
        if ($this->status !== self::STATUS_RUNNING) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_CANCELLED,
            'completed_at' => now(),
        ]);

        return true;
    }

    public function retry(): ReportExecution
    {
        return $this->report->execute($this->parameters);
    }

    public function export(string $format = 'pdf'): string
    {
        if ($this->status !== self::STATUS_COMPLETED) {
            throw new \Exception('Cannot export incomplete execution');
        }

        $exporter = app("App\\Services\\ReportExportService");
        $filePath = $exporter->export($this, $format);

        $this->update([
            'export_format' => $format,
            'export_path' => $filePath,
        ]);

        return $filePath;
    }
}
