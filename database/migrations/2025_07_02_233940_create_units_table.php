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
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->json('short_name');
            $table->json('description')->nullable();
            $table->foreignId('base_unit_id')->nullable()->constrained('units')->onDelete('cascade');
            $table->decimal('conversion_factor', 10, 4)->default(1);
            $table->boolean('is_active')->default(true);
            $table->string('type')->default('weight'); // weight, volume, length, piece, etc.
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'type']);
            $table->index('base_unit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
