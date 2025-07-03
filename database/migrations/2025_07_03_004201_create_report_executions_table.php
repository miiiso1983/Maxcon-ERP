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
        Schema::create('report_executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained()->onDelete('cascade');
            $table->json('parameters')->nullable();
            $table->enum('status', ['pending', 'running', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('executed_by')->constrained('users')->onDelete('cascade');
            $table->longText('result_data')->nullable();
            $table->integer('row_count')->default(0);
            $table->text('error_message')->nullable();
            $table->string('export_format')->nullable();
            $table->string('export_path')->nullable();
            $table->timestamps();

            $table->index(['report_id', 'status']);
            $table->index(['executed_by', 'started_at']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_executions');
    }
};
