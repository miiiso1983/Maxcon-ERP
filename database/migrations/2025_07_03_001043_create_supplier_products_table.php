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
        Schema::create('supplier_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('supplier_sku')->nullable();
            $table->decimal('cost_price', 15, 2);
            $table->integer('lead_time_days')->default(0);
            $table->decimal('minimum_order_quantity', 15, 2)->default(1);
            $table->timestamps();

            $table->unique(['supplier_id', 'product_id']);
            $table->index('supplier_sku');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_products');
    }
};
