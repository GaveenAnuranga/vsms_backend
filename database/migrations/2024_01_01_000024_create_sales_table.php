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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants');
            $table->foreignId('vehicle_id')->constrained('vehicles');
            $table->foreignId('buyer_id')->constrained('buyers');
            $table->date('sale_date');
            $table->decimal('sale_price', 12, 2);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('final_amount', 12, 2);
            $table->foreignId('payment_method_id')->constrained('payment_methods');
            $table->string('invoice_number', 100);
            $table->decimal('commission', 12, 2)->default(0);
            $table->string('salesperson_name', 150);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
