<?php

namespace App\Modules\WhatsApp\Services;

use App\Modules\WhatsApp\Models\WhatsAppMessage;
use App\Modules\WhatsApp\Models\WhatsAppTemplate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class WhatsAppService
{
    private string $apiUrl;
    private ?string $accessToken;
    private ?string $phoneNumberId;
    private ?string $businessAccountId;

    public function __construct()
    {
        $this->apiUrl = config('whatsapp.api_url', 'https://graph.facebook.com/v18.0');
        $this->accessToken = config('whatsapp.access_token');
        $this->phoneNumberId = config('whatsapp.phone_number_id');
        $this->businessAccountId = config('whatsapp.business_account_id');
    }

    /**
     * Send a text message
     */
    public function sendTextMessage(string $to, string $message, string $language = 'en'): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'WhatsApp service is not properly configured'
            ];
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $this->formatPhoneNumber($to),
            'type' => 'text',
            'text' => [
                'body' => $message
            ]
        ];

        return $this->makeApiCall('messages', $payload);
    }

    /**
     * Send a template message
     */
    public function sendTemplateMessage(string $to, string $templateName, array $parameters = [], string $language = 'en'): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $this->formatPhoneNumber($to),
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => [
                    'code' => $language
                ]
            ]
        ];

        if (!empty($parameters)) {
            $payload['template']['components'] = [
                [
                    'type' => 'body',
                    'parameters' => array_map(function ($param) {
                        return ['type' => 'text', 'text' => $param];
                    }, $parameters)
                ]
            ];
        }

        return $this->makeApiCall('messages', $payload);
    }

    /**
     * Send a media message
     */
    public function sendMediaMessage(string $to, string $mediaType, string $mediaUrl, ?string $caption = null): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $this->formatPhoneNumber($to),
            'type' => $mediaType,
            $mediaType => [
                'link' => $mediaUrl
            ]
        ];

        if ($caption && in_array($mediaType, ['image', 'video', 'document'])) {
            $payload[$mediaType]['caption'] = $caption;
        }

        return $this->makeApiCall('messages', $payload);
    }

    /**
     * Send a document message
     */
    public function sendDocument(string $to, string $documentUrl, ?string $filename = null, ?string $caption = null): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $this->formatPhoneNumber($to),
            'type' => 'document',
            'document' => [
                'link' => $documentUrl
            ]
        ];

        if ($filename) {
            $payload['document']['filename'] = $filename;
        }

        if ($caption) {
            $payload['document']['caption'] = $caption;
        }

        return $this->makeApiCall('messages', $payload);
    }

    /**
     * Send an interactive button message
     */
    public function sendButtonMessage(string $to, string $bodyText, array $buttons): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $this->formatPhoneNumber($to),
            'type' => 'interactive',
            'interactive' => [
                'type' => 'button',
                'body' => [
                    'text' => $bodyText
                ],
                'action' => [
                    'buttons' => array_map(function ($button, $index) {
                        return [
                            'type' => 'reply',
                            'reply' => [
                                'id' => $button['id'] ?? "btn_{$index}",
                                'title' => $button['title']
                            ]
                        ];
                    }, $buttons, array_keys($buttons))
                ]
            ]
        ];

        return $this->makeApiCall('messages', $payload);
    }

    /**
     * Send a list message
     */
    public function sendListMessage(string $to, string $bodyText, string $buttonText, array $sections): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $this->formatPhoneNumber($to),
            'type' => 'interactive',
            'interactive' => [
                'type' => 'list',
                'body' => [
                    'text' => $bodyText
                ],
                'action' => [
                    'button' => $buttonText,
                    'sections' => $sections
                ]
            ]
        ];

        return $this->makeApiCall('messages', $payload);
    }

    /**
     * Process WhatsApp message from database
     */
    public function processMessage(WhatsAppMessage $message): bool
    {
        try {
            $response = null;

            switch ($message->message_type) {
                case WhatsAppMessage::TYPE_INVOICE:
                case WhatsAppMessage::TYPE_RECEIPT:
                case WhatsAppMessage::TYPE_PAYMENT_REMINDER:
                    $response = $this->sendBusinessMessage($message);
                    break;

                default:
                    $response = $this->sendTextMessage(
                        $message->recipient_phone,
                        $message->content[$message->language] ?? $message->content['en'],
                        $message->language
                    );
                    break;
            }

            if ($response && isset($response['messages'][0]['id'])) {
                $message->markAsSent($response['messages'][0]['id']);
                return true;
            } else {
                $message->markAsFailed($response['error']['message'] ?? 'Unknown error');
                return false;
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp message failed', [
                'message_id' => $message->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $message->markAsFailed($e->getMessage());
            return false;
        }
    }

    /**
     * Send business-specific message (invoice, receipt, etc.)
     */
    private function sendBusinessMessage(WhatsAppMessage $message): array
    {
        $content = $message->content[$message->language] ?? $message->content['en'];
        
        // If there's media (like PDF invoice), send as document
        if ($message->media_url && $message->media_type === 'document') {
            return $this->sendDocument(
                $message->recipient_phone,
                $message->media_url,
                $this->getDocumentFilename($message),
                $content
            );
        }

        // Otherwise send as text
        return $this->sendTextMessage($message->recipient_phone, $content, $message->language);
    }

    /**
     * Get document filename based on message type
     */
    private function getDocumentFilename(WhatsAppMessage $message): string
    {
        $relatedModel = $message->relatedModel;
        
        switch ($message->message_type) {
            case WhatsAppMessage::TYPE_INVOICE:
                return "Invoice_{$relatedModel->invoice_number}.pdf";
            case WhatsAppMessage::TYPE_RECEIPT:
                return "Receipt_{$relatedModel->receipt_number}.pdf";
            default:
                return "Document_{$message->id}.pdf";
        }
    }

    /**
     * Handle webhook from WhatsApp
     */
    public function handleWebhook(array $data): void
    {
        try {
            if (!isset($data['entry'][0]['changes'][0]['value'])) {
                return;
            }

            $value = $data['entry'][0]['changes'][0]['value'];

            // Handle message status updates
            if (isset($value['statuses'])) {
                foreach ($value['statuses'] as $status) {
                    $this->updateMessageStatus($status);
                }
            }

            // Handle incoming messages
            if (isset($value['messages'])) {
                foreach ($value['messages'] as $incomingMessage) {
                    $this->handleIncomingMessage($incomingMessage);
                }
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp webhook error', [
                'data' => $data,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update message status from webhook
     */
    private function updateMessageStatus(array $status): void
    {
        $message = WhatsAppMessage::where('whatsapp_message_id', $status['id'])->first();
        
        if (!$message) {
            return;
        }

        switch ($status['status']) {
            case 'delivered':
                $message->markAsDelivered();
                break;
            case 'read':
                $message->markAsRead();
                break;
            case 'failed':
                $message->markAsFailed($status['errors'][0]['title'] ?? 'Delivery failed');
                break;
        }
    }

    /**
     * Handle incoming message
     */
    private function handleIncomingMessage(array $messageData): void
    {
        // Log incoming message for future processing
        Log::info('Incoming WhatsApp message', $messageData);
        
        // Here you can implement auto-responses, customer service, etc.
        // For now, we'll just log it
    }

    /**
     * Get message delivery statistics
     */
    public function getDeliveryStats(int $days = 30): array
    {
        $startDate = now()->subDays($days);

        $stats = WhatsAppMessage::where('created_at', '>=', $startDate)
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as sent,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as delivered,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as `read`,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as failed
            ', [
                WhatsAppMessage::STATUS_SENT,
                WhatsAppMessage::STATUS_DELIVERED,
                WhatsAppMessage::STATUS_READ,
                WhatsAppMessage::STATUS_FAILED
            ])
            ->first();

        return [
            'total' => $stats->total ?? 0,
            'sent' => $stats->sent ?? 0,
            'delivered' => $stats->delivered ?? 0,
            'read' => $stats->read ?? 0,
            'failed' => $stats->failed ?? 0,
            'delivery_rate' => $stats->sent > 0 ? ($stats->delivered / $stats->sent) * 100 : 0,
            'read_rate' => $stats->delivered > 0 ? ($stats->read / $stats->delivered) * 100 : 0,
            'failure_rate' => $stats->total > 0 ? ($stats->failed / $stats->total) * 100 : 0,
        ];
    }

    /**
     * Queue messages for bulk sending
     */
    public function queueBulkMessages(array $messages): int
    {
        $queued = 0;
        
        foreach ($messages as $messageData) {
            try {
                $message = WhatsAppMessage::create(array_merge($messageData, [
                    'status' => WhatsAppMessage::STATUS_QUEUED,
                    'max_retries' => 3,
                ]));
                
                $queued++;
            } catch (\Exception $e) {
                Log::error('Failed to queue WhatsApp message', [
                    'data' => $messageData,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        return $queued;
    }

    /**
     * Process queued messages
     */
    public function processQueuedMessages(int $limit = 50): array
    {
        $messages = WhatsAppMessage::readyToSend()
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get();

        $results = [
            'processed' => 0,
            'successful' => 0,
            'failed' => 0,
        ];

        foreach ($messages as $message) {
            $results['processed']++;
            
            if ($this->processMessage($message)) {
                $results['successful']++;
            } else {
                $results['failed']++;
            }
            
            // Rate limiting - WhatsApp allows 1000 messages per second
            usleep(1000); // 1ms delay
        }

        return $results;
    }

    /**
     * Format phone number for WhatsApp API
     */
    private function formatPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Add Iraq country code if not present
        if (!str_starts_with($phone, '964')) {
            if (str_starts_with($phone, '0')) {
                $phone = '964' . substr($phone, 1);
            } else {
                $phone = '964' . $phone;
            }
        }
        
        return $phone;
    }

    /**
     * Make API call to WhatsApp
     */
    private function makeApiCall(string $endpoint, array $payload): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'WhatsApp service is not properly configured'
            ];
        }

        $url = "{$this->apiUrl}/{$this->phoneNumberId}/{$endpoint}";

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->accessToken}",
            'Content-Type' => 'application/json',
        ])->post($url, $payload);

        $result = $response->json();

        if (!$response->successful()) {
            Log::error('WhatsApp API error', [
                'url' => $url,
                'payload' => $payload,
                'response' => $result,
                'status' => $response->status()
            ]);
        }

        return $result;
    }

    /**
     * Validate WhatsApp webhook signature
     */
    public function validateWebhookSignature(string $payload, string $signature): bool
    {
        $expectedSignature = 'sha256=' . hash_hmac('sha256', $payload, config('whatsapp.webhook_secret'));
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Get WhatsApp Business Account info
     */
    public function getBusinessAccountInfo(): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'WhatsApp service is not properly configured'
            ];
        }

        $url = "{$this->apiUrl}/{$this->businessAccountId}";

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->accessToken}",
        ])->get($url, [
            'fields' => 'id,name,timezone_offset_min,message_template_namespace'
        ]);

        return $response->json();
    }

    /**
     * Check if service is properly configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->accessToken) && 
               !empty($this->phoneNumberId) && 
               !empty($this->businessAccountId);
    }
}
