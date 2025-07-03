<?php

namespace App\Modules\Financial\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class CollectionActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'collection_id',
        'activity_type',
        'description',
        'activity_date',
        'user_id',
        'metadata',
    ];

    protected $casts = [
        'activity_date' => 'datetime',
        'metadata' => 'array',
    ];

    const TYPE_CONTACT_ATTEMPT = 'contact_attempt';
    const TYPE_PAYMENT_RECEIVED = 'payment_received';
    const TYPE_PAYMENT_CONFIRMED = 'payment_confirmed';
    const TYPE_PAYMENT_FAILED = 'payment_failed';
    const TYPE_PAYMENT_REFUNDED = 'payment_refunded';
    const TYPE_FOLLOW_UP_SCHEDULED = 'follow_up_scheduled';
    const TYPE_DISCOUNT_APPLIED = 'discount_applied';
    const TYPE_WRITTEN_OFF = 'written_off';
    const TYPE_STATUS_CHANGED = 'status_changed';
    const TYPE_PRIORITY_CHANGED = 'priority_changed';
    const TYPE_NOTE_ADDED = 'note_added';

    // Relationships
    public function collection()
    {
        return $this->belongsTo(Collection::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Accessors
    public function getTypeColorAttribute(): string
    {
        return match($this->activity_type) {
            self::TYPE_CONTACT_ATTEMPT => 'info',
            self::TYPE_PAYMENT_RECEIVED => 'success',
            self::TYPE_PAYMENT_CONFIRMED => 'success',
            self::TYPE_PAYMENT_FAILED => 'danger',
            self::TYPE_PAYMENT_REFUNDED => 'warning',
            self::TYPE_FOLLOW_UP_SCHEDULED => 'primary',
            self::TYPE_DISCOUNT_APPLIED => 'info',
            self::TYPE_WRITTEN_OFF => 'danger',
            self::TYPE_STATUS_CHANGED => 'secondary',
            self::TYPE_PRIORITY_CHANGED => 'warning',
            self::TYPE_NOTE_ADDED => 'light',
            default => 'secondary',
        };
    }

    public function getTypeIconAttribute(): string
    {
        return match($this->activity_type) {
            self::TYPE_CONTACT_ATTEMPT => 'fas fa-phone',
            self::TYPE_PAYMENT_RECEIVED => 'fas fa-money-bill',
            self::TYPE_PAYMENT_CONFIRMED => 'fas fa-check-circle',
            self::TYPE_PAYMENT_FAILED => 'fas fa-times-circle',
            self::TYPE_PAYMENT_REFUNDED => 'fas fa-undo',
            self::TYPE_FOLLOW_UP_SCHEDULED => 'fas fa-calendar-plus',
            self::TYPE_DISCOUNT_APPLIED => 'fas fa-percentage',
            self::TYPE_WRITTEN_OFF => 'fas fa-trash',
            self::TYPE_STATUS_CHANGED => 'fas fa-exchange-alt',
            self::TYPE_PRIORITY_CHANGED => 'fas fa-exclamation-triangle',
            self::TYPE_NOTE_ADDED => 'fas fa-sticky-note',
            default => 'fas fa-info-circle',
        };
    }
}
