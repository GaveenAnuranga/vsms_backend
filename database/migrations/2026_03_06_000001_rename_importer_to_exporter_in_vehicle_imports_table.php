<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_imports', function (Blueprint $table) {
            $table->renameColumn('importer_name', 'exporter_name');
            $table->renameColumn('importer_contact', 'exporter_contact');
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_imports', function (Blueprint $table) {
            $table->renameColumn('exporter_name', 'importer_name');
            $table->renameColumn('exporter_contact', 'importer_contact');
        });
    }
};
