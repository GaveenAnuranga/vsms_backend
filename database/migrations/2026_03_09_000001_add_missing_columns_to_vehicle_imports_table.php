<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_imports', function (Blueprint $table) {
            if (!Schema::hasColumn('vehicle_imports', 'exporter_name')) {
                $table->string('exporter_name', 150)->nullable();
            }
            if (!Schema::hasColumn('vehicle_imports', 'exporter_contact')) {
                $table->string('exporter_contact', 30)->nullable();
            }
            if (!Schema::hasColumn('vehicle_imports', 'register_notification')) {
                $table->boolean('register_notification')->default(false);
            }
            if (!Schema::hasColumn('vehicle_imports', 'register_notification_date')) {
                $table->date('register_notification_date')->nullable();
            }
            if (!Schema::hasColumn('vehicle_imports', 'notification_dismissed')) {
                $table->boolean('notification_dismissed')->default(false);
            }
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_imports', function (Blueprint $table) {
            $table->dropColumn([
                'exporter_name',
                'exporter_contact',
                'register_notification',
                'register_notification_date',
                'notification_dismissed',
            ]);
        });
    }
};
