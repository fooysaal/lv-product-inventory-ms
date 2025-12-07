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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->string('sku', 50)->unique();
            $table->text('description')->nullable();
            $table->foreignId('category_id')->constrained('categories')->onDelete('restrict');
            $table->foreignId('unit_id')->constrained('units')->onDelete('restrict');
            $table->decimal('cost_price', 12, 2)->default(0);
            $table->decimal('selling_price', 12, 2)->default(0);
            $table->decimal('min_stock_level', 10, 2)->default(0); // Reorder level
            $table->decimal('max_stock_level', 10, 2)->nullable(); // Maximum stock
            $table->string('barcode', 100)->nullable()->unique();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('category_id');
            $table->index('unit_id');
            $table->index('sku');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
