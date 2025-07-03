<?php

namespace App\Modules\WhatsApp\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\WhatsApp\Models\WhatsAppMessage;
use App\Modules\WhatsApp\Models\WhatsAppTemplate;
use App\Modules\WhatsApp\Services\WhatsAppService;
use App\Modules\Customer\Models\Customer;
use App\Modules\Sales\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WhatsAppController extends Controller
{
    protected WhatsAppService $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    public function dashboard()
    {
        // Get overview statistics
        $overview = $this->getOverviewStats();
        
        // Get recent messages
        $recentMessages = $this->getRecentMessages();
        
        // Get delivery statistics
        $deliveryStats = $this->whatsappService->getDeliveryStats(30);
        
        // Get message trends
        $trends = $this->getMessageTrends();
        
        // Get template usage
        $templateUsage = $this->getTemplateUsage();

        return view('tenant.whatsapp.dashboard', compact(
            'overview', 'recentMessages', 'deliveryStats', 'trends', 'templateUsage'
        ));
    }

    public function messages()
    {
        $messages = WhatsAppMessage::with(['customer', 'template'])
            ->latest()
            ->paginate(20);

        $stats = WhatsAppMessage::getDeliveryStats();
        $types = WhatsAppMessage::getMessageTypes();

        return view('tenant.whatsapp.messages.index', compact(
            'messages', 'stats', 'types'
        ));
    }

    public function createMessage()
    {
        $customers = Customer::active()->get();
        $templates = WhatsAppTemplate::active()->get();
        $types = WhatsAppMessage::getMessageTypes();
        $priorities = WhatsAppMessage::getPriorities();
        
        return view('tenant.whatsapp.messages.create', compact(
            'customers', 'templates', 'types', 'priorities'
        ));
    }

    public function storeMessage(Request $request)
    {
        $request->validate([
            'recipient_phone' => 'required|string',
            'recipient_name' => 'required|string|max:255',
            'customer_id' => 'sometimes|exists:customers,id',
            'message_type' => 'required|in:' . implode(',', array_keys(WhatsAppMessage::getMessageTypes())),
            'content' => 'required|array',
            'content.en' => 'required|string',
            'priority' => 'required|in:' . implode(',', array_keys(WhatsAppMessage::getPriorities())),
            'scheduled_at' => 'sometimes|date|after:now',
        ]);

        DB::beginTransaction();
        try {
            $message = WhatsAppMessage::create(array_merge($request->all(), [
                'user_id' => auth()->id(),
                'language' => app()->getLocale(),
                'max_retries' => 3,
                'status' => $request->scheduled_at ? WhatsAppMessage::STATUS_PENDING : WhatsAppMessage::STATUS_PENDING,
            ]));

            // If not scheduled, try to send immediately
            if (!$request->scheduled_at) {
                $this->whatsappService->processMessage($message);
            }

            DB::commit();
            
            return redirect()->route('whatsapp.messages')
                ->with('success', 'Message created successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to create message: ' . $e->getMessage()]);
        }
    }

    public function showMessage(WhatsAppMessage $message)
    {
        $message->load(['customer', 'template', 'user']);
        
        return view('tenant.whatsapp.messages.show', compact('message'));
    }

    public function resendMessage(WhatsAppMessage $message)
    {
        if (!$message->can_retry) {
            return response()->json(['error' => 'Message cannot be retried'], 400);
        }

        $message->retry();
        $success = $this->whatsappService->processMessage($message);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Message resent successfully' : 'Failed to resend message',
        ]);
    }

    public function templates()
    {
        $templates = WhatsAppTemplate::latest()->paginate(20);
        $categories = WhatsAppTemplate::getCategories();
        $types = WhatsAppTemplate::getTemplateTypes();

        return view('tenant.whatsapp.templates.index', compact(
            'templates', 'categories', 'types'
        ));
    }

    public function createTemplate()
    {
        $categories = WhatsAppTemplate::getCategories();
        $types = WhatsAppTemplate::getTemplateTypes();
        
        return view('tenant.whatsapp.templates.create', compact(
            'categories', 'types'
        ));
    }

    public function storeTemplate(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:whats_app_templates,name',
            'description' => 'required|array',
            'description.en' => 'required|string',
            'template_type' => 'required|in:' . implode(',', array_keys(WhatsAppTemplate::getTemplateTypes())),
            'category' => 'required|in:' . implode(',', array_keys(WhatsAppTemplate::getCategories())),
            'content' => 'required|array',
            'content.en' => 'required|string',
            'variables' => 'sometimes|array',
        ]);

        $template = WhatsAppTemplate::create(array_merge($request->all(), [
            'language' => 'multi',
            'status' => WhatsAppTemplate::STATUS_DRAFT,
            'approval_status' => WhatsAppTemplate::APPROVAL_PENDING,
            'is_active' => false,
        ]));

        return redirect()->route('whatsapp.templates')
            ->with('success', 'Template created successfully');
    }

    public function showTemplate(WhatsAppTemplate $template)
    {
        $template->load('messages');
        
        return view('tenant.whatsapp.templates.show', compact('template'));
    }

    public function sendInvoice(Request $request, Sale $sale)
    {
        $request->validate([
            'include_pdf' => 'sometimes|boolean',
        ]);

        try {
            $message = WhatsAppMessage::createInvoiceMessage($sale);
            
            // Add PDF attachment if requested
            if ($request->include_pdf) {
                $pdfUrl = route('sales.invoice.pdf', $sale);
                $message->update([
                    'media_url' => $pdfUrl,
                    'media_type' => 'document',
                ]);
            }

            $success = $this->whatsappService->processMessage($message);

            return response()->json([
                'success' => $success,
                'message' => $success ? 'Invoice sent successfully' : 'Failed to send invoice',
                'whatsapp_message_id' => $message->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send invoice via WhatsApp', [
                'sale_id' => $sale->id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to send invoice'], 500);
        }
    }

    public function sendPaymentReminder(Request $request, Sale $sale)
    {
        $request->validate([
            'days_past_due' => 'sometimes|integer|min:0',
        ]);

        try {
            $daysPastDue = $request->days_past_due ?? 0;
            $message = WhatsAppMessage::createPaymentReminder($sale, $daysPastDue);
            
            $success = $this->whatsappService->processMessage($message);

            return response()->json([
                'success' => $success,
                'message' => $success ? 'Payment reminder sent successfully' : 'Failed to send payment reminder',
                'whatsapp_message_id' => $message->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send payment reminder via WhatsApp', [
                'sale_id' => $sale->id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to send payment reminder'], 500);
        }
    }

    public function bulkSend(Request $request)
    {
        $request->validate([
            'customer_ids' => 'required|array',
            'customer_ids.*' => 'exists:customers,id',
            'template_id' => 'required|exists:whats_app_templates,id',
            'variables' => 'sometimes|array',
            'scheduled_at' => 'sometimes|date|after:now',
        ]);

        $template = WhatsAppTemplate::findOrFail($request->template_id);
        $customers = Customer::whereIn('id', $request->customer_ids)->get();
        
        $messages = [];
        foreach ($customers as $customer) {
            $variables = array_merge($request->variables ?? [], [
                'customer_name' => $customer->name,
                'company_name' => config('app.name'),
                'phone_number' => config('company.phone'),
            ]);

            $content = $template->renderContent($variables);

            $messages[] = [
                'recipient_phone' => $customer->phone,
                'recipient_name' => $customer->name,
                'customer_id' => $customer->id,
                'template_id' => $template->id,
                'message_type' => WhatsAppMessage::TYPE_PROMOTIONAL,
                'content' => $content,
                'priority' => WhatsAppMessage::PRIORITY_NORMAL,
                'language' => app()->getLocale(),
                'scheduled_at' => $request->scheduled_at,
                'user_id' => auth()->id(),
            ];
        }

        $queued = $this->whatsappService->queueBulkMessages($messages);
        
        return response()->json([
            'success' => true,
            'message' => "Successfully queued {$queued} messages",
            'queued_count' => $queued,
        ]);
    }

    public function webhook(Request $request)
    {
        // Verify webhook (for initial setup)
        if ($request->has('hub_mode') && $request->hub_mode === 'subscribe') {
            if ($request->hub_verify_token === config('whatsapp.webhook_verify_token')) {
                return response($request->hub_challenge);
            }
            return response('Unauthorized', 401);
        }

        // Validate signature
        $signature = $request->header('X-Hub-Signature-256');
        if (!$this->whatsappService->validateWebhookSignature($request->getContent(), $signature)) {
            return response('Unauthorized', 401);
        }

        // Process webhook
        $this->whatsappService->handleWebhook($request->all());
        
        return response('OK');
    }

    public function processQueue()
    {
        $results = $this->whatsappService->processQueuedMessages(50);
        
        return response()->json([
            'success' => true,
            'results' => $results,
        ]);
    }

    public function settings()
    {
        $isConfigured = $this->whatsappService->isConfigured();
        $businessInfo = $isConfigured ? $this->whatsappService->getBusinessAccountInfo() : null;
        
        return view('tenant.whatsapp.settings', compact('isConfigured', 'businessInfo'));
    }

    private function getOverviewStats(): array
    {
        $today = now()->format('Y-m-d');
        $thisMonth = now()->format('Y-m');

        return [
            'total_messages' => WhatsAppMessage::count(),
            'messages_today' => WhatsAppMessage::where('created_at', 'like', $today . '%')->count(),
            'messages_this_month' => WhatsAppMessage::where('created_at', 'like', $thisMonth . '%')->count(),
            'pending_messages' => WhatsAppMessage::pending()->count(),
            'failed_messages' => WhatsAppMessage::failed()->count(),
            'active_templates' => WhatsAppTemplate::active()->count(),
            'delivery_rate' => $this->calculateDeliveryRate(),
        ];
    }

    private function getRecentMessages(): \Illuminate\Database\Eloquent\Collection
    {
        return WhatsAppMessage::with(['customer', 'template'])
            ->latest()
            ->take(10)
            ->get();
    }

    private function getMessageTrends(): array
    {
        $trends = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            
            $trends[] = [
                'date' => $date,
                'sent' => WhatsAppMessage::where('created_at', 'like', $date . '%')
                    ->where('status', WhatsAppMessage::STATUS_SENT)
                    ->count(),
                'delivered' => WhatsAppMessage::where('created_at', 'like', $date . '%')
                    ->where('status', WhatsAppMessage::STATUS_DELIVERED)
                    ->count(),
                'failed' => WhatsAppMessage::where('created_at', 'like', $date . '%')
                    ->where('status', WhatsAppMessage::STATUS_FAILED)
                    ->count(),
            ];
        }

        return $trends;
    }

    private function getTemplateUsage(): array
    {
        return WhatsAppTemplate::active()
            ->orderByDesc('usage_count')
            ->take(5)
            ->get()
            ->map(function ($template) {
                return [
                    'name' => $template->name,
                    'usage_count' => $template->usage_count,
                    'last_used' => $template->last_used_at,
                ];
            })
            ->toArray();
    }

    private function calculateDeliveryRate(): float
    {
        $sent = WhatsAppMessage::sent()->count();
        $delivered = WhatsAppMessage::where('status', WhatsAppMessage::STATUS_DELIVERED)->count();
        
        return $sent > 0 ? ($delivered / $sent) * 100 : 0;
    }
}
