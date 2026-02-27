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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('vehicle_code', 50)->unique();
            $table->string('make', 100);
            $table->string('model', 100);
            $table->string('sub_model', 100)->nullable();
            $table->enum('vehicle_type', ['Car', 'SUV', 'Van', 'Bus', 'Lorry', 'Truck', 'Pickup', 'Minivan', 'Coupe', 'Sedan', 'Hatchback', 'Wagon']);
            $table->integer('year');
            $table->string('color', 50);
            $table->string('country_of_origin', 100);
            $table->enum('fuel_type', ['Gasoline', 'Diesel', 'Electric', 'Hybrid', 'Plug-in Hybrid']);
            $table->integer('mileage');
            $table->enum('transmission_type', ['Automatic', 'Manual', 'CVT', 'Semi-Automatic']);
            $table->string('engine_size', 50)->nullable();
            $table->string('vin', 100)->nullable();
            $table->enum('registration_type', ['Registered', 'Unregistered']);
            $table->decimal('price', 12, 2);
            $table->foreignId('dealer_id')->nullable()->constrained('dealers')->onDelete('set null');
            $table->enum('status', ['Available', 'Sold', 'Transferred', 'Reserved'])->default('Available');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
