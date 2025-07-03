<?php

namespace App\Modules\WhatsApp\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Translatable\HasTranslations;
use App\Models\User;
use App\Modules\Customer\Models\Customer;
use App\Modules\Sales\Models\Sale;

class WhatsAppMessage extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'recipient_phone',
        'recipient_name',
        'customer_id',
        'user_id',
        'message_type',
        'template_id',
        'content',
        'media_url',
        'media_type',
        'status',
        'whatsapp_message_id',
        'delivery_status',
        'read_status',
        'sent_at',
        'delivered_at',
        'read_at',
        'failed_at',
        'failure_reason',
        'retry_count',
        'max_retries',
        'scheduled_at',
        'priority',
        'related_model_type',
        'related_model_id',
        'language',
        'metadata',
    ];

    protected $casts = [
        'content' => 'array',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
        'failed_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'retry_count' => 'integer',
        'max_retries' => 'integer',
        'metadata' => 'array',
    ];

    public $translatable = ['content'];

    // Message Types
    const TYPE_INVOICE = 'invoice';
    const TYPE_RECEIPT = 'receipt';
    const TYPE_PAYMENT_REMINDER = 'payment_reminder';
    const TYPE_WELCOME = 'welcome';
    const TYPE_ORDER_CONFIRMATION = 'order_confirmation';
    const TYPE_DELIVERY_UPDATE = 'delivery_update';
    const TYPE_APPOINTMENT_REMINDER = 'appointment_reminder';
    const TYPE_PROMOTIONAL = 'promotional';
    const TYPE_NOTIFICATION = 'notification';
    const TYPE_CUSTOM = 'custom';

    // Status
    const STATUS_PENDING = 'pending';
    const STATUS_QUEUED = 'queued';
    const STATUS_SENT = 'sent';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_READ = 'read';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';

    // Delivery Status
    const DELIVERY_PENDING = 'pending';
    const DELIVERY_SENT = 'sent';
    const DELIVERY_DELIVERED = 'delivered';
    const DELIVERY_FAILED = 'failed';

    // Priority Levels
    const PRIORITY_LOW = 'low';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    // Media Types
    const MEDIA_IMAGE = 'image';
    const MEDIA_DOCUMENT = 'document';
    const MEDIA_VIDEO = 'video';
    const MEDIA_AUDIO = 'audio';

    // Relationships
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function template()
    {
        return $this->belongsTo(WhatsAppTemplate::class, 'template_id');
    }

    public function relatedModel()
    {
        return $this->morphTo('related_model', 'related_model_type', 'related_model_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeQueued($query)
    {
        return $query->where('status', self::STATUS_QUEUED);
    }

    public function scopeSent($query)
    {
        return $query->where('status', self::STATUS_SENT);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function scopeScheduled($query)
    {
        return $query->whereNotNull('scheduled_at')
                     ->where('scheduled_at', '>', now());
    }

    public function scopeReadyToSend($query)
    {
        return $query->where('status', self::STATUS_PENDING)
                     ->where(function ($q) {
                         $q->whereNull('scheduled_at')
                           ->orWhere('scheduled_at', '<=', now());
                     });
    }

    public function scopeByType($query, $type)
    {
        return $query->where('message_type', $type);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    // Accessors
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_QUEUED => 'info',
            self::STATUS_SENT => 'primary',
            self::STATUS_DELIVERED => 'success',
            self::STATUS_READ => 'success',
            self::STATUS_FAILED => 'danger',
            self::STATUS_CANCELLED => 'secondary',
            default => 'light',
        };
    }

    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            self::PRIORITY_URGENT => 'danger',
            self::PRIORITY_HIGH => 'warning',
            self::PRIORITY_NORMAL => 'primary',
            self::PRIORITY_LOW => 'secondary',
            default => 'light',
        };
    }

    public function getFormattedPhoneAttribute(): string
    {
        $phone = preg_replace('/[^0-9]/', '', $this->recipient_phone);
        
        // Add Iraq country code if not present
        if (!str_starts_with($phone, '964')) {
            if (str_starts_with($phone, '0')) {
                $phone = '964' . substr($phone, 1);
            } else {
                $phone = '964' . $phone;
            }
        }
        
        return '+' . $phone;
    }

    public function getIsScheduledAttribute(): bool
    {
        return $this->scheduled_at && $this->scheduled_at->isFuture();
    }

    public function getCanRetryAttribute(): bool
    {
        return $this->status === self::STATUS_FAILED && 
               $this->retry_count < $this->max_retries;
    }

    // Methods
    public function markAsSent(?string $whatsappMessageId = null): void
    {
        $this->update([
            'status' => self::STATUS_SENT,
            'delivery_status' => self::DELIVERY_SENT,
            'whatsapp_message_id' => $whatsappMessageId,
            'sent_at' => now(),
        ]);
    }

    public function markAsDelivered(): void
    {
        $this->update([
            'status' => self::STATUS_DELIVERED,
            'delivery_status' => self::DELIVERY_DELIVERED,
            'delivered_at' => now(),
        ]);
    }

    public function markAsRead(): void
    {
        $this->update([
            'status' => self::STATUS_READ,
            'read_status' => true,
            'read_at' => now(),
        ]);
    }

    public function markAsFailed(?string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'delivery_status' => self::DELIVERY_FAILED,
            'failed_at' => now(),
            'failure_reason' => $reason,
            'retry_count' => $this->retry_count + 1,
        ]);
    }

    public function cancel(): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
        ]);
    }

    public function retry(): void
    {
        if ($this->can_retry) {
            $this->update([
                'status' => self::STATUS_PENDING,
                'failed_at' => null,
                'failure_reason' => null,
            ]);
        }
    }

    public function schedule(\DateTime $dateTime): void
    {
        $this->update([
            'scheduled_at' => $dateTime,
            'status' => self::STATUS_PENDING,
        ]);
    }

    public function addMetadata(string $key, $value): void
    {
        $metadata = $this->metadata ?? [];
        $metadata[$key] = $value;
        $this->metadata = $metadata;
        $this->save();
    }

    public function getMetadata(string $key, $default = null)
    {
        return $this->metadata[$key] ?? $default;
    }

    // Static Methods
    public static function getMessageTypes(): array
    {
        return [
            self::TYPE_INVOICE => 'Invoice',
            self::TYPE_RECEIPT => 'Receipt',
            self::TYPE_PAYMENT_REMINDER => 'Payment Reminder',
            self::TYPE_WELCOME => 'Welcome Message',
            self::TYPE_ORDER_CONFIRMATION => 'Order Confirmation',
            self::TYPE_DELIVERY_UPDATE => 'Delivery Update',
            self::TYPE_APPOINTMENT_REMINDER => 'Appointment Reminder',
            self::TYPE_PROMOTIONAL => 'Promotional',
            self::TYPE_NOTIFICATION => 'Notification',
            self::TYPE_CUSTOM => 'Custom Message',
        ];
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_QUEUED => 'Queued',
            self::STATUS_SENT => 'Sent',
            self::STATUS_DELIVERED => 'Delivered',
            self::STATUS_READ => 'Read',
            self::STATUS_FAILED => 'Failed',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    public static function getPriorities(): array
    {
        return [
            self::PRIORITY_LOW => 'Low',
            self::PRIORITY_NORMAL => 'Normal',
            self::PRIORITY_HIGH => 'High',
            self::PRIORITY_URGENT => 'Urgent',
        ];
    }

    public static function createInvoiceMessage(Sale $sale): self
    {
        $customer = $sale->customer;
        
        return self::create([
            'recipient_phone' => $customer->phone,
            'recipient_name' => $customer->name,
            'customer_id' => $customer->id,
            'message_type' => self::TYPE_INVOICE,
            'content' => [
                'en' => "Dear {$customer->name}, your invoice #{$sale->invoice_number} for " . number_format($sale->total_amount, 2) . " IQD is ready. Thank you for your business!",
                'ar' => "عزيزي {$customer->name}، فاتورتك رقم #{$sale->invoice_number} بمبلغ " . number_format($sale->total_amount, 2) . " دينار عراقي جاهزة. شكراً لك!",
                'ku' => "بەڕێز {$customer->name}، پسوولەکەت ژمارە #{$sale->invoice_number} بە بڕی " . number_format($sale->total_amount, 2) . " دیناری عێراقی ئامادەیە. سوپاس!",
            ],
            'priority' => self::PRIORITY_HIGH,
            'related_model_type' => Sale::class,
            'related_model_id' => $sale->id,
            'language' => app()->getLocale(),
            'max_retries' => 3,
        ]);
    }

    public static function createPaymentReminder(Sale $sale, int $daysPastDue = 0): self
    {
        $customer = $sale->customer;
        $urgency = $daysPastDue > 30 ? 'urgent' : ($daysPastDue > 7 ? 'final' : 'friendly');
        
        $messages = [
            'friendly' => [
                'en' => "Dear {$customer->name}, this is a friendly reminder that your payment of " . number_format($sale->total_amount, 2) . " IQD for invoice #{$sale->invoice_number} is due. Please settle at your earliest convenience.",
                'ar' => "عزيزي {$customer->name}، هذا تذكير ودود بأن دفعتك البالغة " . number_format($sale->total_amount, 2) . " دينار عراقي للفاتورة رقم #{$sale->invoice_number} مستحقة. يرجى التسوية في أقرب وقت ممكن.",
                'ku' => "بەڕێز {$customer->name}، ئەمە بیرخستنەوەیەکی دۆستانەیە کە پارەدانەکەت بە بڕی " . number_format($sale->total_amount, 2) . " دیناری عێراقی بۆ پسوولە ژمارە #{$sale->invoice_number} کاتی هاتووە.",
            ],
            'final' => [
                'en' => "URGENT: Dear {$customer->name}, your payment of " . number_format($sale->total_amount, 2) . " IQD for invoice #{$sale->invoice_number} is {$daysPastDue} days overdue. Please contact us immediately to avoid service interruption.",
                'ar' => "عاجل: عزيزي {$customer->name}، دفعتك البالغة " . number_format($sale->total_amount, 2) . " دينار عراقي للفاتورة رقم #{$sale->invoice_number} متأخرة {$daysPastDue} يوماً. يرجى الاتصال بنا فوراً لتجنب انقطاع الخدمة.",
                'ku' => "پەلە: بەڕێز {$customer->name}، پارەدانەکەت بە بڕی " . number_format($sale->total_amount, 2) . " دیناری عێراقی بۆ پسوولە ژمارە #{$sale->invoice_number} {$daysPastDue} ڕۆژ دواکەوتووە.",
            ],
            'urgent' => [
                'en' => "FINAL NOTICE: Dear {$customer->name}, your payment of " . number_format($sale->total_amount, 2) . " IQD for invoice #{$sale->invoice_number} is {$daysPastDue} days overdue. This is your final notice before collection action.",
                'ar' => "إشعار أخير: عزيزي {$customer->name}، دفعتك البالغة " . number_format($sale->total_amount, 2) . " دينار عراقي للفاتورة رقم #{$sale->invoice_number} متأخرة {$daysPastDue} يوماً. هذا إشعارك الأخير قبل اتخاذ إجراءات التحصيل.",
                'ku' => "ئاگاداری کۆتایی: بەڕێز {$customer->name}، پارەدانەکەت بە بڕی " . number_format($sale->total_amount, 2) . " دیناری عێراقی بۆ پسوولە ژمارە #{$sale->invoice_number} {$daysPastDue} ڕۆژ دواکەوتووە.",
            ],
        ];

        return self::create([
            'recipient_phone' => $customer->phone,
            'recipient_name' => $customer->name,
            'customer_id' => $customer->id,
            'message_type' => self::TYPE_PAYMENT_REMINDER,
            'content' => $messages[$urgency],
            'priority' => $urgency === 'urgent' ? self::PRIORITY_URGENT : self::PRIORITY_HIGH,
            'related_model_type' => Sale::class,
            'related_model_id' => $sale->id,
            'language' => app()->getLocale(),
            'max_retries' => 3,
            'metadata' => [
                'days_past_due' => $daysPastDue,
                'urgency_level' => $urgency,
            ],
        ]);
    }

    public static function getDeliveryStats(): array
    {
        $total = self::count();
        $sent = self::sent()->count();
        $delivered = self::where('status', self::STATUS_DELIVERED)->count();
        $failed = self::failed()->count();

        return [
            'total_messages' => $total,
            'sent_messages' => $sent,
            'delivered_messages' => $delivered,
            'failed_messages' => $failed,
            'delivery_rate' => $sent > 0 ? ($delivered / $sent) * 100 : 0,
            'failure_rate' => $total > 0 ? ($failed / $total) * 100 : 0,
        ];
    }
}
