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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants');
            $table->foreignId('vehicle_id')->constrained('vehicles');
            $table->date('purchase_date');
            $table->decimal('purchase_price', 12, 2);
            $table->foreignId('payment_method_id')->constrained('payment_methods');
            $table->string('invoice_number', 100);
            $table->decimal('tax_amount', 12, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
