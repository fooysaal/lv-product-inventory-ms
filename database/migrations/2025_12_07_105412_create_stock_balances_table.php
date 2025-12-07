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
        Schema::create('stock_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->decimal('quantity', 12, 2)->default(0);
            $table->decimal('reserved_quantity', 12, 2)->default(0); // Reserved for pending orders
            $table->decimal('available_quantity', 12, 2)->default(0); // quantity - reserved_quantity
            $table->timestamp('last_updated')->nullable();
            $table->timestamps();

            // Composite unique key - one balance record per product per warehouse
            $table->unique(['product_id', 'warehouse_id']);
            $table->index('product_id');
            $table->index('warehouse_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_balances');
    }
};
