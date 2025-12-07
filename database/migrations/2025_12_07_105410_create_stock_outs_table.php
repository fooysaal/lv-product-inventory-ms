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
        Schema::create('stock_outs', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number', 50)->unique();
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('restrict');
            $table->decimal('quantity', 12, 2);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->string('customer_name', 200)->nullable();
            $table->string('order_number', 100)->nullable();
            $table->date('issued_date');
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict'); // Stock Executive
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('restrict'); // Stock Manager
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('product_id');
            $table->index('warehouse_id');
            $table->index('status');
            $table->index('created_by');
            $table->index('approved_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_outs');
    }
};
