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
        Schema::create('predictions', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->json('description')->nullable();
            $table->enum('prediction_type', ['demand_forecast', 'price_optimization', 'customer_behavior', 'inventory_optimization', 'sales_forecast', 'churn_prediction']);
            $table->enum('model_type', ['linear_regression', 'polynomial_regression', 'moving_average', 'exponential_smoothing', 'arima', 'neural_network']);
            $table->string('target_entity_type');
            $table->unsignedBigInteger('target_entity_id');
            $table->longText('input_data');
            $table->longText('prediction_result');
            $table->decimal('confidence_score', 5, 4)->default(0);
            $table->decimal('accuracy_score', 5, 4)->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->date('prediction_date');
            $table->longText('actual_result')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->json('model_parameters')->nullable();
            $table->integer('training_data_period')->nullable();
            $table->json('meta_data')->nullable();
            $table->timestamps();

            $table->index(['prediction_type', 'status']);
            $table->index(['target_entity_type', 'target_entity_id']);
            $table->index(['created_by', 'prediction_date']);
            $table->index('confidence_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('predictions');
    }
};
