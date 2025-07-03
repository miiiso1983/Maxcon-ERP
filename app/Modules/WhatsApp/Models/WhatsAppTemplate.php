<?php

namespace App\Modules\WhatsApp\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Translatable\HasTranslations;

class WhatsAppTemplate extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'name',
        'description',
        'template_type',
        'category',
        'content',
        'variables',
        'media_type',
        'media_url',
        'language',
        'status',
        'whatsapp_template_id',
        'approval_status',
        'rejection_reason',
        'is_active',
        'usage_count',
        'last_used_at',
        'metadata',
    ];

    protected $casts = [
        'content' => 'array',
        'variables' => 'array',
        'is_active' => 'boolean',
        'usage_count' => 'integer',
        'last_used_at' => 'datetime',
        'metadata' => 'array',
    ];

    public $translatable = ['content', 'description'];

    // Template Types
    const TYPE_TEXT = 'text';
    const TYPE_MEDIA = 'media';
    const TYPE_INTERACTIVE = 'interactive';
    const TYPE_LOCATION = 'location';

    // Categories
    const CATEGORY_TRANSACTIONAL = 'transactional';
    const CATEGORY_MARKETING = 'marketing';
    const CATEGORY_UTILITY = 'utility';
    const CATEGORY_AUTHENTICATION = 'authentication';

    // Status
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_DISABLED = 'disabled';

    // Approval Status
    const APPROVAL_PENDING = 'pending';
    const APPROVAL_APPROVED = 'approved';
    const APPROVAL_REJECTED = 'rejected';

    // Relationships
    public function messages()
    {
        return $this->hasMany(WhatsAppMessage::class, 'template_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                     ->where('status', self::STATUS_APPROVED);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('template_type', $type);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeApproved($query)
    {
        return $query->where('approval_status', self::APPROVAL_APPROVED);
    }

    // Accessors
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'secondary',
            self::STATUS_PENDING => 'warning',
            self::STATUS_APPROVED => 'success',
            self::STATUS_REJECTED => 'danger',
            self::STATUS_DISABLED => 'dark',
            default => 'light',
        };
    }

    public function getApprovalColorAttribute(): string
    {
        return match($this->approval_status) {
            self::APPROVAL_PENDING => 'warning',
            self::APPROVAL_APPROVED => 'success',
            self::APPROVAL_REJECTED => 'danger',
            default => 'light',
        };
    }

    // Methods
    public function renderContent(array $variables = []): array
    {
        $content = $this->content;
        
        foreach ($content as $language => $text) {
            foreach ($variables as $key => $value) {
                $content[$language] = str_replace("{{$key}}", $value, $content[$language]);
            }
        }
        
        return $content;
    }

    public function incrementUsage(): void
    {
        $this->increment('usage_count');
        $this->update(['last_used_at' => now()]);
    }

    public function approve(): void
    {
        $this->update([
            'approval_status' => self::APPROVAL_APPROVED,
            'status' => self::STATUS_APPROVED,
        ]);
    }

    public function reject(string $reason = null): void
    {
        $this->update([
            'approval_status' => self::APPROVAL_REJECTED,
            'status' => self::STATUS_REJECTED,
            'rejection_reason' => $reason,
        ]);
    }

    public function disable(): void
    {
        $this->update([
            'status' => self::STATUS_DISABLED,
            'is_active' => false,
        ]);
    }

    public function enable(): void
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'is_active' => true,
        ]);
    }

    public function getVariableList(): array
    {
        return $this->variables ?? [];
    }

    public function validateVariables(array $provided): array
    {
        $required = $this->getVariableList();
        $missing = [];
        
        foreach ($required as $variable) {
            if (!isset($provided[$variable])) {
                $missing[] = $variable;
            }
        }
        
        return $missing;
    }

    // Static Methods
    public static function getTemplateTypes(): array
    {
        return [
            self::TYPE_TEXT => 'Text',
            self::TYPE_MEDIA => 'Media',
            self::TYPE_INTERACTIVE => 'Interactive',
            self::TYPE_LOCATION => 'Location',
        ];
    }

    public static function getCategories(): array
    {
        return [
            self::CATEGORY_TRANSACTIONAL => 'Transactional',
            self::CATEGORY_MARKETING => 'Marketing',
            self::CATEGORY_UTILITY => 'Utility',
            self::CATEGORY_AUTHENTICATION => 'Authentication',
        ];
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PENDING => 'Pending Approval',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_DISABLED => 'Disabled',
        ];
    }

    public static function createInvoiceTemplate(): self
    {
        return self::create([
            'name' => 'Invoice Notification',
            'description' => [
                'en' => 'Template for sending invoice notifications to customers',
                'ar' => 'قالب لإرسال إشعارات الفواتير للعملاء',
                'ku' => 'قاڵب بۆ ناردنی ئاگاداری پسوولە بۆ کڕیارەکان',
            ],
            'template_type' => self::TYPE_TEXT,
            'category' => self::CATEGORY_TRANSACTIONAL,
            'content' => [
                'en' => 'Dear {{customer_name}}, your invoice #{{invoice_number}} for {{amount}} IQD is ready. Total: {{total_amount}} IQD. Thank you for your business!',
                'ar' => 'عزيزي {{customer_name}}، فاتورتك رقم #{{invoice_number}} بمبلغ {{amount}} دينار عراقي جاهزة. المجموع: {{total_amount}} دينار عراقي. شكراً لك!',
                'ku' => 'بەڕێز {{customer_name}}، پسوولەکەت ژمارە #{{invoice_number}} بە بڕی {{amount}} دیناری عێراقی ئامادەیە. کۆی گشتی: {{total_amount}} دیناری عێراقی. سوپاس!',
            ],
            'variables' => ['customer_name', 'invoice_number', 'amount', 'total_amount'],
            'language' => 'multi',
            'status' => self::STATUS_APPROVED,
            'approval_status' => self::APPROVAL_APPROVED,
            'is_active' => true,
        ]);
    }

    public static function createPaymentReminderTemplate(): self
    {
        return self::create([
            'name' => 'Payment Reminder',
            'description' => [
                'en' => 'Template for payment reminder messages',
                'ar' => 'قالب لرسائل تذكير الدفع',
                'ku' => 'قاڵب بۆ پەیامەکانی بیرخستنەوەی پارەدان',
            ],
            'template_type' => self::TYPE_TEXT,
            'category' => self::CATEGORY_TRANSACTIONAL,
            'content' => [
                'en' => 'Dear {{customer_name}}, this is a reminder that your payment of {{amount}} IQD for invoice #{{invoice_number}} is {{status}}. Please settle at your earliest convenience.',
                'ar' => 'عزيزي {{customer_name}}، هذا تذكير بأن دفعتك البالغة {{amount}} دينار عراقي للفاتورة رقم #{{invoice_number}} {{status}}. يرجى التسوية في أقرب وقت ممكن.',
                'ku' => 'بەڕێز {{customer_name}}، ئەمە بیرخستنەوەیەکە کە پارەدانەکەت بە بڕی {{amount}} دیناری عێراقی بۆ پسوولە ژمارە #{{invoice_number}} {{status}}.',
            ],
            'variables' => ['customer_name', 'amount', 'invoice_number', 'status'],
            'language' => 'multi',
            'status' => self::STATUS_APPROVED,
            'approval_status' => self::APPROVAL_APPROVED,
            'is_active' => true,
        ]);
    }

    public static function createWelcomeTemplate(): self
    {
        return self::create([
            'name' => 'Welcome Message',
            'description' => [
                'en' => 'Welcome message for new customers',
                'ar' => 'رسالة ترحيب للعملاء الجدد',
                'ku' => 'پەیامی بەخێرهاتن بۆ کڕیارە نوێیەکان',
            ],
            'template_type' => self::TYPE_TEXT,
            'category' => self::CATEGORY_UTILITY,
            'content' => [
                'en' => 'Welcome to {{company_name}}, {{customer_name}}! We\'re excited to serve you. For any assistance, contact us at {{phone_number}}.',
                'ar' => 'مرحباً بك في {{company_name}}، {{customer_name}}! نحن متحمسون لخدمتك. للحصول على أي مساعدة، اتصل بنا على {{phone_number}}.',
                'ku' => 'بەخێرهاتن بۆ {{company_name}}، {{customer_name}}! ئێمە دڵخۆشین کە خزمەتتان دەکەین. بۆ هەر یارمەتییەک، پەیوەندیمان پێوە بکەن لە {{phone_number}}.',
            ],
            'variables' => ['company_name', 'customer_name', 'phone_number'],
            'language' => 'multi',
            'status' => self::STATUS_APPROVED,
            'approval_status' => self::APPROVAL_APPROVED,
            'is_active' => true,
        ]);
    }

    public static function createOrderConfirmationTemplate(): self
    {
        return self::create([
            'name' => 'Order Confirmation',
            'description' => [
                'en' => 'Order confirmation message for customers',
                'ar' => 'رسالة تأكيد الطلب للعملاء',
                'ku' => 'پەیامی پشتڕاستکردنەوەی داواکاری بۆ کڕیارەکان',
            ],
            'template_type' => self::TYPE_TEXT,
            'category' => self::CATEGORY_TRANSACTIONAL,
            'content' => [
                'en' => 'Dear {{customer_name}}, your order #{{order_number}} has been confirmed. Total: {{total_amount}} IQD. Estimated delivery: {{delivery_date}}.',
                'ar' => 'عزيزي {{customer_name}}، تم تأكيد طلبك رقم #{{order_number}}. المجموع: {{total_amount}} دينار عراقي. التسليم المتوقع: {{delivery_date}}.',
                'ku' => 'بەڕێز {{customer_name}}، داواکاریەکەت ژمارە #{{order_number}} پشتڕاست کرایەوە. کۆی گشتی: {{total_amount}} دیناری عێراقی. گەیاندنی چاوەڕوانکراو: {{delivery_date}}.',
            ],
            'variables' => ['customer_name', 'order_number', 'total_amount', 'delivery_date'],
            'language' => 'multi',
            'status' => self::STATUS_APPROVED,
            'approval_status' => self::APPROVAL_APPROVED,
            'is_active' => true,
        ]);
    }
}
