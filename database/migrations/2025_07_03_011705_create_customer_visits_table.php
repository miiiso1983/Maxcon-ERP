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
        Schema::create('customer_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_rep_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->date('visit_date');
            $table->datetime('visit_time');
            $table->enum('visit_type', ['routine', 'follow_up', 'introduction', 'product_launch', 'complaint', 'collection', 'emergency']);
            $table->enum('purpose', ['sales', 'relationship', 'education', 'support', 'feedback', 'collection']);
            $table->enum('status', ['planned', 'in_progress', 'completed', 'cancelled', 'missed', 'rescheduled'])->default('planned');
            $table->datetime('check_in_time')->nullable();
            $table->datetime('check_out_time')->nullable();
            $table->json('check_in_location')->nullable();
            $table->json('check_out_location')->nullable();
            $table->decimal('distance_traveled', 8, 2)->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->text('notes')->nullable();
            $table->json('outcomes')->nullable();
            $table->date('next_visit_date')->nullable();
            $table->json('products_discussed')->nullable();
            $table->json('samples_given')->nullable();
            $table->json('orders_taken')->nullable();
            $table->json('feedback_received')->nullable();
            $table->json('competitor_info')->nullable();
            $table->json('photos')->nullable();
            $table->json('documents')->nullable();
            $table->json('gps_coordinates')->nullable();
            $table->string('weather_conditions')->nullable();
            $table->boolean('is_planned')->default(false);
            $table->foreignId('planned_by')->nullable()->constrained('medical_reps')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('medical_reps')->onDelete('set null');
            $table->json('meta_data')->nullable();
            $table->timestamps();

            $table->index(['medical_rep_id', 'visit_date']);
            $table->index(['customer_id', 'visit_date']);
            $table->index(['visit_date', 'status']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_visits');
    }
};
