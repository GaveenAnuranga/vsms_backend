<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds importer details and registration notification fields.
     * Makes legacy fields nullable for backward compatibility.
     */
    public function up(): void
    {
        Schema::table('vehicle_imports', function (Blueprint $table) {
            // Make legacy fields nullable for backward compatibility
            $table->string('chassis_number', 100)->nullable()->change();
            $table->string('engine_number', 100)->nullable()->change();
            $table->integer('import_year')->nullable()->change();

            // New fields for the updated form
            $table->string('importer_name', 150)->nullable()->after('vehicle_id');
            $table->string('importer_contact', 30)->nullable()->after('importer_name');
            $table->boolean('register_notification')->default(false)->after('importer_contact');
            $table->date('register_notification_date')->nullable()->after('register_notification');
            $table->boolean('notification_dismissed')->default(false)->after('register_notification_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_imports', function (Blueprint $table) {
            $table->dropColumn(['importer_name', 'importer_contact', 'register_notification', 'register_notification_date', 'notification_dismissed']);
            $table->string('chassis_number', 100)->nullable(false)->change();
            $table->string('engine_number', 100)->nullable(false)->change();
            $table->integer('import_year')->nullable(false)->change();
        });
    }
};
