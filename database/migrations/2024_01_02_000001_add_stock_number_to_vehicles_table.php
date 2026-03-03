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
        if (!Schema::hasColumn('vehicles', 'stock_number')) {
            Schema::table('vehicles', function (Blueprint $table) {
                $table->string('stock_number', 50)->nullable()->unique()->after('vehicle_code');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropUnique(['stock_number']);
            $table->dropColumn('stock_number');
        });
    }
};
