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
        Schema::create('vehicle_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->unique()->constrained('vehicles')->onDelete('cascade');
            $table->string('exporter_name', 150)->nullable();
            $table->string('exporter_contact', 30)->nullable();
            $table->boolean('register_notification')->default(false);
            $table->date('register_notification_date')->nullable();
            $table->boolean('notification_dismissed')->default(false);
            $table->string('chassis_number', 100)->nullable();
            $table->string('engine_number', 100)->nullable();
            $table->date('imported_date')->nullable();
            $table->integer('import_year')->nullable();
            $table->string('auction_grade', 10)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_imports');
    }
};
