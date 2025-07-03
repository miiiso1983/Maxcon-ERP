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
        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->string('collection_number')->unique();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('collector_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('collection_date');
            $table->timestamp('due_date');
            $table->decimal('amount_due', 15, 2);
            $table->decimal('amount_collected', 15, 2)->default(0);
            $table->enum('collection_method', ['cash', 'bank_transfer', 'cheque', 'card', 'digital_wallet', 'payment_plan'])->default('cash');
            $table->enum('status', ['pending', 'in_progress', 'collected', 'partial', 'cancelled', 'written_off'])->default('pending');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->json('notes')->nullable();
            $table->timestamp('follow_up_date')->nullable();
            $table->integer('contact_attempts')->default(0);
            $table->timestamp('last_contact_date')->nullable();
            $table->unsignedBigInteger('payment_plan_id')->nullable();
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('penalty_amount', 15, 2)->default(0);
            $table->string('currency', 3)->default('IQD');
            $table->decimal('exchange_rate', 10, 4)->default(1);
            $table->json('meta_data')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'priority']);
            $table->index(['due_date', 'status']);
            $table->index(['follow_up_date', 'status']);
            $table->index('collection_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collections');
    }
};
