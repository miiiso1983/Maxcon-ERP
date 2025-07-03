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
        Schema::create('inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compliance_item_id')->constrained()->onDelete('cascade');
            $table->enum('inspection_type', ['routine', 'follow_up', 'complaint', 'random', 'renewal', 'initial', 'special']);
            $table->string('inspector_name');
            $table->string('inspector_organization');
            $table->string('inspector_contact')->nullable();
            $table->datetime('scheduled_date');
            $table->datetime('actual_date')->nullable();
            $table->decimal('duration_hours', 5, 2)->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled', 'postponed'])->default('scheduled');
            $table->enum('result', ['passed', 'failed', 'conditional', 'pending'])->nullable();
            $table->decimal('score', 5, 2)->nullable();
            $table->json('findings')->nullable();
            $table->json('recommendations')->nullable();
            $table->json('corrective_actions')->nullable();
            $table->boolean('follow_up_required')->default(false);
            $table->date('follow_up_date')->nullable();
            $table->json('documents')->nullable();
            $table->json('photos')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->string('currency', 3)->default('IQD');
            $table->boolean('certificate_issued')->default(false);
            $table->string('certificate_number')->nullable();
            $table->date('certificate_expiry')->nullable();
            $table->date('next_inspection_date')->nullable();
            $table->foreignId('conducted_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->json('meta_data')->nullable();
            $table->timestamps();

            $table->index(['compliance_item_id', 'status']);
            $table->index(['scheduled_date', 'status']);
            $table->index('inspection_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspections');
    }
};
