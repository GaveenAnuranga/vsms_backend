<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds new owner-related fields and makes legacy fields nullable.
     */
    public function up(): void
    {
        Schema::table('vehicle_registrations', function (Blueprint $table) {
            // Make legacy fields nullable for backward compatibility
            $table->string('registration_number', 100)->nullable()->change();
            $table->string('number_plate', 50)->nullable()->change();
            $table->date('registration_date')->nullable()->change();

            // New fields for the updated form
            $table->string('vehicle_number', 50)->nullable()->after('vehicle_id');
            $table->unsignedSmallInteger('registration_year')->nullable()->after('vehicle_number');
            $table->string('owner_name', 150)->nullable()->after('registration_year');
            $table->string('owner_contact', 30)->nullable()->after('owner_name');
            $table->text('service_record')->nullable()->after('owner_contact');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_registrations', function (Blueprint $table) {
            $table->dropColumn(['vehicle_number', 'registration_year', 'owner_name', 'owner_contact', 'service_record']);
            $table->string('registration_number', 100)->nullable(false)->change();
            $table->string('number_plate', 50)->nullable(false)->change();
            $table->date('registration_date')->nullable(false)->change();
        });
    }
};
