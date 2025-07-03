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
        Schema::create('supplier_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('evaluation_date');
            $table->decimal('quality_rating', 3, 1)->default(0);
            $table->decimal('delivery_rating', 3, 1)->default(0);
            $table->decimal('service_rating', 3, 1)->default(0);
            $table->decimal('price_rating', 3, 1)->default(0);
            $table->decimal('communication_rating', 3, 1)->default(0);
            $table->decimal('overall_rating', 3, 1)->default(0);
            $table->text('comments')->nullable();
            $table->text('recommendations')->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('evaluation_period_start')->nullable();
            $table->date('evaluation_period_end')->nullable();
            $table->timestamps();

            $table->index(['supplier_id', 'evaluation_date']);
            $table->index(['is_active', 'overall_rating']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_evaluations');
    }
};
