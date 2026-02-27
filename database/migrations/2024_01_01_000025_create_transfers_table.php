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
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants');
            $table->foreignId('vehicle_id')->constrained('vehicles');
            $table->foreignId('from_dealer_id')->nullable()->constrained('dealers');
            $table->foreignId('to_dealer_id')->nullable()->constrained('dealers');
            $table->date('transfer_date');
            $table->decimal('transfer_price', 12, 2);
            $table->decimal('transport_cost', 12, 2)->default(0);
            $table->enum('status', ['pending', 'completed'])->default('pending');
            $table->string('responsible_person', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};
