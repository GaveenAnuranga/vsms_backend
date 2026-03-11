<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('vehicle_imports', 'imported_date')) {
            Schema::table('vehicle_imports', function (Blueprint $table) {
                $table->date('imported_date')->nullable()->after('engine_number');
            });
        }
    }

    public function down(): void
    {
        // Column is managed by the original create migration; nothing to drop here.
    }
};
