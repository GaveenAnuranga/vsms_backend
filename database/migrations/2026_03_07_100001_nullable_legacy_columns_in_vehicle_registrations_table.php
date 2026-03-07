<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_registrations', function (Blueprint $table) {
            $table->string('registration_number', 100)->nullable()->change();
            $table->string('number_plate', 50)->nullable()->change();
            $table->date('registration_date')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_registrations', function (Blueprint $table) {
            $table->string('registration_number', 100)->nullable(false)->change();
            $table->string('number_plate', 50)->nullable(false)->change();
            $table->date('registration_date')->nullable(false)->change();
        });
    }
};
