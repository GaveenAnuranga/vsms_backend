<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_registrations', function (Blueprint $table) {
            if (!Schema::hasColumn('vehicle_registrations', 'vehicle_number')) {
                $table->string('vehicle_number', 50)->nullable()->after('vehicle_id');
            }
            if (!Schema::hasColumn('vehicle_registrations', 'registration_year')) {
                $table->unsignedSmallInteger('registration_year')->nullable()->after('vehicle_number');
            }
            if (!Schema::hasColumn('vehicle_registrations', 'owner_name')) {
                $table->string('owner_name', 150)->nullable()->after('registration_year');
            }
            if (!Schema::hasColumn('vehicle_registrations', 'owner_contact')) {
                $table->string('owner_contact', 30)->nullable()->after('owner_name');
            }
            if (!Schema::hasColumn('vehicle_registrations', 'service_record')) {
                $table->text('service_record')->nullable()->after('owner_contact');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_registrations', function (Blueprint $table) {
            $table->dropColumn([
                'vehicle_number',
                'registration_year',
                'owner_name',
                'owner_contact',
                'service_record',
            ]);
        });
    }
};
