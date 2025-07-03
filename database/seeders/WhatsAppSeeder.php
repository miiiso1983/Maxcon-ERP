<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Modules\WhatsApp\Models\WhatsAppTemplate;
use App\Modules\WhatsApp\Models\WhatsAppMessage;
use App\Modules\Customer\Models\Customer;
use App\Modules\Sales\Models\Sale;
use App\Models\User;

class WhatsAppSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create WhatsApp Templates
        $this->createTemplates();

        // Create sample messages
        $this->createSampleMessages();

        $this->command->info('WhatsApp sample data seeded successfully!');
    }

    private function createTemplates(): void
    {
        // Create default templates
        WhatsAppTemplate::createInvoiceTemplate();
        WhatsAppTemplate::createPaymentReminderTemplate();
        WhatsAppTemplate::createWelcomeTemplate();
        WhatsAppTemplate::createOrderConfirmationTemplate();

        // Create additional custom templates
        WhatsAppTemplate::create([
            'name' => 'Delivery Update',
            'description' => [
                'en' => 'Template for delivery status updates',
                'ar' => 'قالب لتحديثات حالة التسليم',
                'ku' => 'قاڵب بۆ نوێکردنەوەی دۆخی گەیاندن',
            ],
            'template_type' => WhatsAppTemplate::TYPE_TEXT,
            'category' => WhatsAppTemplate::CATEGORY_TRANSACTIONAL,
            'content' => [
                'en' => 'Dear {{customer_name}}, your order #{{order_number}} is {{status}}. Expected delivery: {{delivery_date}}. Track your order: {{tracking_url}}',
                'ar' => 'عزيزي {{customer_name}}، طلبك رقم #{{order_number}} {{status}}. التسليم المتوقع: {{delivery_date}}. تتبع طلبك: {{tracking_url}}',
                'ku' => 'بەڕێز {{customer_name}}، داواکاریەکەت ژمارە #{{order_number}} {{status}}. گەیاندنی چاوەڕوانکراو: {{delivery_date}}. شوێنکەوتنی داواکاریەکەت: {{tracking_url}}',
            ],
            'variables' => ['customer_name', 'order_number', 'status', 'delivery_date', 'tracking_url'],
            'language' => 'multi',
            'status' => WhatsAppTemplate::STATUS_APPROVED,
            'approval_status' => WhatsAppTemplate::APPROVAL_APPROVED,
            'is_active' => true,
        ]);

        WhatsAppTemplate::create([
            'name' => 'Appointment Reminder',
            'description' => [
                'en' => 'Template for appointment reminders',
                'ar' => 'قالب لتذكير المواعيد',
                'ku' => 'قاڵب بۆ بیرخستنەوەی چاوپێکەوتن',
            ],
            'template_type' => WhatsAppTemplate::TYPE_TEXT,
            'category' => WhatsAppTemplate::CATEGORY_UTILITY,
            'content' => [
                'en' => 'Dear {{customer_name}}, this is a reminder for your appointment on {{appointment_date}} at {{appointment_time}}. Location: {{location}}. Please confirm your attendance.',
                'ar' => 'عزيزي {{customer_name}}، هذا تذكير بموعدك في {{appointment_date}} في {{appointment_time}}. الموقع: {{location}}. يرجى تأكيد حضورك.',
                'ku' => 'بەڕێز {{customer_name}}، ئەمە بیرخستنەوەیەکە بۆ چاوپێکەوتنەکەت لە {{appointment_date}} لە {{appointment_time}}. شوێن: {{location}}. تکایە ئامادەبوونت پشتڕاست بکەرەوە.',
            ],
            'variables' => ['customer_name', 'appointment_date', 'appointment_time', 'location'],
            'language' => 'multi',
            'status' => WhatsAppTemplate::STATUS_APPROVED,
            'approval_status' => WhatsAppTemplate::APPROVAL_APPROVED,
            'is_active' => true,
        ]);

        WhatsAppTemplate::create([
            'name' => 'Promotional Offer',
            'description' => [
                'en' => 'Template for promotional offers and discounts',
                'ar' => 'قالب للعروض الترويجية والخصومات',
                'ku' => 'قاڵب بۆ پێشکەشکراوە بازرگانییەکان و داشکاندنەکان',
            ],
            'template_type' => WhatsAppTemplate::TYPE_TEXT,
            'category' => WhatsAppTemplate::CATEGORY_MARKETING,
            'content' => [
                'en' => 'Special offer for {{customer_name}}! Get {{discount}}% off on {{product_category}}. Valid until {{expiry_date}}. Use code: {{promo_code}}. Shop now!',
                'ar' => 'عرض خاص لـ {{customer_name}}! احصل على خصم {{discount}}% على {{product_category}}. صالح حتى {{expiry_date}}. استخدم الكود: {{promo_code}}. تسوق الآن!',
                'ku' => 'پێشکەشکراوی تایبەت بۆ {{customer_name}}! {{discount}}% داشکاندن لەسەر {{product_category}}. دروستە تا {{expiry_date}}. کۆدەکە بەکاربهێنە: {{promo_code}}. ئێستا بکڕە!',
            ],
            'variables' => ['customer_name', 'discount', 'product_category', 'expiry_date', 'promo_code'],
            'language' => 'multi',
            'status' => WhatsAppTemplate::STATUS_APPROVED,
            'approval_status' => WhatsAppTemplate::APPROVAL_APPROVED,
            'is_active' => true,
        ]);
    }

    private function createSampleMessages(): void
    {
        $customers = Customer::take(10)->get();
        $users = User::take(3)->get();
        $templates = WhatsAppTemplate::active()->get();

        if ($customers->isEmpty() || $users->isEmpty()) {
            $this->command->warn('No customers or users found. Skipping message creation.');
            return;
        }

        // Create various types of messages
        foreach ($customers->take(5) as $customer) {
            // Invoice message
            WhatsAppMessage::create([
                'recipient_phone' => $customer->phone,
                'recipient_name' => $customer->name,
                'customer_id' => $customer->id,
                'user_id' => $users->random()->id,
                'message_type' => WhatsAppMessage::TYPE_INVOICE,
                'template_id' => $templates->where('name', 'Invoice Notification')->first()?->id,
                'content' => [
                    'en' => "Dear {$customer->name}, your invoice #INV-" . rand(1000, 9999) . " for " . rand(100, 1000) . " IQD is ready. Thank you for your business!",
                    'ar' => "عزيزي {$customer->name}، فاتورتك رقم #INV-" . rand(1000, 9999) . " بمبلغ " . rand(100, 1000) . " دينار عراقي جاهزة. شكراً لك!",
                    'ku' => "بەڕێز {$customer->name}، پسوولەکەت ژمارە #INV-" . rand(1000, 9999) . " بە بڕی " . rand(100, 1000) . " دیناری عێراقی ئامادەیە. سوپاس!",
                ],
                'status' => WhatsAppMessage::STATUS_DELIVERED,
                'delivery_status' => WhatsAppMessage::DELIVERY_DELIVERED,
                'priority' => WhatsAppMessage::PRIORITY_HIGH,
                'sent_at' => now()->subHours(rand(1, 24)),
                'delivered_at' => now()->subHours(rand(1, 23)),
                'language' => 'en',
                'max_retries' => 3,
            ]);

            // Welcome message
            if (rand(1, 10) > 7) {
                WhatsAppMessage::create([
                    'recipient_phone' => $customer->phone,
                    'recipient_name' => $customer->name,
                    'customer_id' => $customer->id,
                    'user_id' => $users->random()->id,
                    'message_type' => WhatsAppMessage::TYPE_WELCOME,
                    'template_id' => $templates->where('name', 'Welcome Message')->first()?->id,
                    'content' => [
                        'en' => "Welcome to Maxcon ERP, {$customer->name}! We're excited to serve you. For any assistance, contact us at +964 770 123 4567.",
                        'ar' => "مرحباً بك في Maxcon ERP، {$customer->name}! نحن متحمسون لخدمتك. للحصول على أي مساعدة، اتصل بنا على +964 770 123 4567.",
                        'ku' => "بەخێرهاتن بۆ Maxcon ERP، {$customer->name}! ئێمە دڵخۆشین کە خزمەتتان دەکەین. بۆ هەر یارمەتییەک، پەیوەندیمان پێوە بکەن لە +964 770 123 4567.",
                    ],
                    'status' => WhatsAppMessage::STATUS_READ,
                    'delivery_status' => WhatsAppMessage::DELIVERY_DELIVERED,
                    'read_status' => true,
                    'priority' => WhatsAppMessage::PRIORITY_NORMAL,
                    'sent_at' => now()->subDays(rand(1, 7)),
                    'delivered_at' => now()->subDays(rand(1, 6)),
                    'read_at' => now()->subDays(rand(1, 5)),
                    'language' => 'en',
                    'max_retries' => 3,
                ]);
            }
        }

        // Create some payment reminders
        foreach ($customers->take(3) as $customer) {
            WhatsAppMessage::create([
                'recipient_phone' => $customer->phone,
                'recipient_name' => $customer->name,
                'customer_id' => $customer->id,
                'user_id' => $users->random()->id,
                'message_type' => WhatsAppMessage::TYPE_PAYMENT_REMINDER,
                'template_id' => $templates->where('name', 'Payment Reminder')->first()?->id,
                'content' => [
                    'en' => "Dear {$customer->name}, this is a reminder that your payment of " . rand(500, 2000) . " IQD for invoice #INV-" . rand(1000, 9999) . " is due. Please settle at your earliest convenience.",
                    'ar' => "عزيزي {$customer->name}، هذا تذكير بأن دفعتك البالغة " . rand(500, 2000) . " دينار عراقي للفاتورة رقم #INV-" . rand(1000, 9999) . " مستحقة. يرجى التسوية في أقرب وقت ممكن.",
                    'ku' => "بەڕێز {$customer->name}، ئەمە بیرخستنەوەیەکە کە پارەدانەکەت بە بڕی " . rand(500, 2000) . " دیناری عێراقی بۆ پسوولە ژمارە #INV-" . rand(1000, 9999) . " کاتی هاتووە.",
                ],
                'status' => WhatsAppMessage::STATUS_SENT,
                'delivery_status' => WhatsAppMessage::DELIVERY_SENT,
                'priority' => WhatsAppMessage::PRIORITY_HIGH,
                'sent_at' => now()->subHours(rand(1, 12)),
                'language' => 'en',
                'max_retries' => 3,
                'metadata' => [
                    'days_past_due' => rand(1, 30),
                    'urgency_level' => 'friendly',
                ],
            ]);
        }

        // Create some pending messages
        foreach ($customers->take(2) as $customer) {
            WhatsAppMessage::create([
                'recipient_phone' => $customer->phone,
                'recipient_name' => $customer->name,
                'customer_id' => $customer->id,
                'user_id' => $users->random()->id,
                'message_type' => WhatsAppMessage::TYPE_PROMOTIONAL,
                'content' => [
                    'en' => "Special offer for {$customer->name}! Get 20% off on all medical supplies. Valid until " . now()->addDays(7)->format('Y-m-d') . ". Use code: SAVE20. Shop now!",
                    'ar' => "عرض خاص لـ {$customer->name}! احصل على خصم 20% على جميع المستلزمات الطبية. صالح حتى " . now()->addDays(7)->format('Y-m-d') . ". استخدم الكود: SAVE20. تسوق الآن!",
                    'ku' => "پێشکەشکراوی تایبەت بۆ {$customer->name}! 20% داشکاندن لەسەر هەموو پێداویستییە پزیشکییەکان. دروستە تا " . now()->addDays(7)->format('Y-m-d') . ". کۆدەکە بەکاربهێنە: SAVE20. ئێستا بکڕە!",
                ],
                'status' => WhatsAppMessage::STATUS_PENDING,
                'priority' => WhatsAppMessage::PRIORITY_NORMAL,
                'scheduled_at' => now()->addHours(rand(1, 6)),
                'language' => 'en',
                'max_retries' => 3,
            ]);
        }

        // Create some failed messages
        WhatsAppMessage::create([
            'recipient_phone' => '+964770999999', // Invalid number
            'recipient_name' => 'Test Customer',
            'user_id' => $users->random()->id,
            'message_type' => WhatsAppMessage::TYPE_NOTIFICATION,
            'content' => [
                'en' => 'This is a test message that failed to deliver.',
                'ar' => 'هذه رسالة تجريبية فشل في تسليمها.',
                'ku' => 'ئەمە پەیامێکی تاقیکردنەوەیە کە شکستی هێنا لە گەیاندن.',
            ],
            'status' => WhatsAppMessage::STATUS_FAILED,
            'delivery_status' => WhatsAppMessage::DELIVERY_FAILED,
            'priority' => WhatsAppMessage::PRIORITY_NORMAL,
            'sent_at' => now()->subHours(2),
            'failed_at' => now()->subHours(2),
            'failure_reason' => 'Invalid phone number format',
            'retry_count' => 3,
            'language' => 'en',
            'max_retries' => 3,
        ]);

        // Update template usage counts
        foreach ($templates as $template) {
            $usageCount = WhatsAppMessage::where('template_id', $template->id)->count();
            if ($usageCount > 0) {
                $template->update([
                    'usage_count' => $usageCount,
                    'last_used_at' => WhatsAppMessage::where('template_id', $template->id)
                        ->latest('sent_at')
                        ->value('sent_at') ?? now(),
                ]);
            }
        }
    }
}
