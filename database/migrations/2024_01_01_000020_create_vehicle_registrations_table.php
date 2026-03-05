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
        Schema::create('vehicle_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->unique()->constrained('vehicles')->onDelete('cascade');
            $table->string('vehicle_number', 50)->nullable();
            $table->unsignedSmallInteger('registration_year')->nullable();
            $table->string('owner_name', 150)->nullable();
            $table->string('owner_contact', 30)->nullable();
            $table->text('service_record')->nullable();
            $table->string('registration_number', 100)->nullable();
            $table->string('number_plate', 50)->nullable();
            $table->date('registration_date')->nullable();
            $table->integer('number_of_previous_owners')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_registrations');
    }
};
