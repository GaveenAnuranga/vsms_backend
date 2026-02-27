<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('car_purchases', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('vehicle_id');
            $table->date('purchase_date');
            $table->decimal('purchase_price', 15, 2);
            $table->unsignedBigInteger('payment_method_id');
            $table->string('invoice_number', 100);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->string('branch', 150)->nullable();
            $table->string('document_path')->nullable();
            $table->text('tax_details')->nullable();
            $table->timestamps();

            // Indexes for common lookups
            $table->index('tenant_id');
            $table->index('vehicle_id');
            $table->index('payment_method_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('car_purchases');
    }
};
