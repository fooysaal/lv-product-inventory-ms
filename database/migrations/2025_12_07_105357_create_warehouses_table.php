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
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 20)->unique();
            $table->text('address')->nullable();
            $table->string('city', 50)->nullable();
            $table->string('state', 50)->nullable();
            $table->string('country', 50)->default('Bangladesh');
            $table->string('postal_code', 10)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->foreignId('manager_id')->nullable()->constrained('users')->onDelete('set null'); // Stock Manager
            $table->decimal('capacity', 10, 2)->nullable(); // Total capacity
            $table->string('capacity_unit', 20)->nullable(); // sq ft, cubic meters, etc.
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('manager_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};
