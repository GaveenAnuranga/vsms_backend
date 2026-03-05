<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('car_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants');
            $table->foreignId('vehicle_id')->constrained('vehicles');
            $table->date('purchase_date');
            $table->decimal('purchase_price', 15, 2);
            $table->foreignId('payment_method_id')->constrained('payment_methods');
            $table->string('invoice_number', 100);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->string('branch', 150)->nullable();
            $table->string('document_path', 255)->nullable();
            $table->text('tax_details')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_purchases');
    }
};
