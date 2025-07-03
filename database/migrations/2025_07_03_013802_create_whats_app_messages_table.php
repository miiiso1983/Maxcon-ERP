<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('whats_app_messages', function (Blueprint $table) {
            $table->id();
            $table->string('recipient_phone');
            $table->string('recipient_name');
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('message_type', ['invoice', 'receipt', 'payment_reminder', 'welcome', 'order_confirmation', 'delivery_update', 'appointment_reminder', 'promotional', 'notification', 'custom']);
            $table->unsignedBigInteger('template_id')->nullable();
            $table->json('content');
            $table->string('media_url')->nullable();
            $table->enum('media_type', ['image', 'document', 'video', 'audio'])->nullable();
            $table->enum('status', ['pending', 'queued', 'sent', 'delivered', 'read', 'failed', 'cancelled'])->default('pending');
            $table->string('whatsapp_message_id')->nullable();
            $table->enum('delivery_status', ['pending', 'sent', 'delivered', 'failed'])->default('pending');
            $table->boolean('read_status')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->integer('retry_count')->default(0);
            $table->integer('max_retries')->default(3);
            $table->timestamp('scheduled_at')->nullable();
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->string('related_model_type')->nullable();
            $table->unsignedBigInteger('related_model_id')->nullable();
            $table->string('language', 5)->default('en');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['recipient_phone', 'status']);
            $table->index(['customer_id', 'message_type']);
            $table->index(['status', 'priority']);
            $table->index(['scheduled_at', 'status']);
            $table->index(['related_model_type', 'related_model_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whats_app_messages');
    }
};
