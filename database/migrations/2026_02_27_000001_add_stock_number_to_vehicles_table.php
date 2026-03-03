<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add the column only if it doesn't exist yet
        // (2024_01_02 migration may have already created it)
        if (!Schema::hasColumn('vehicles', 'stock_number')) {
            Schema::table('vehicles', function (Blueprint $table) {
                $table->string('stock_number', 5)->nullable()->after('vehicle_code');
            });
        }

        // Back-fill any rows that still have no stock_number
        $vehicles = DB::table('vehicles')->whereNull('stock_number')->get(['id']);
        foreach ($vehicles as $vehicle) {
            do {
                $code = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 5));
            } while (DB::table('vehicles')->where('stock_number', $code)->exists());

            DB::table('vehicles')->where('id', $vehicle->id)->update(['stock_number' => $code]);
        }

        // Ensure column is non-nullable with a unique index
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('stock_number', 5)->nullable(false)->change();
        });
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
