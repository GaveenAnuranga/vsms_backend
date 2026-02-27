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
        Schema::table('vehicles', function (Blueprint $table) {
            // Add stock_number after vehicle_code; nullable initially so existing rows
            // can be back-filled before adding the unique constraint.
            $table->string('stock_number', 5)->nullable()->after('vehicle_code');
        });

        // Back-fill existing rows: generate a unique 5-char uppercase alphanumeric code
        $vehicles = DB::table('vehicles')->whereNull('stock_number')->get(['id']);
        foreach ($vehicles as $vehicle) {
            do {
                $code = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 5));
            } while (DB::table('vehicles')->where('stock_number', $code)->exists());

            DB::table('vehicles')->where('id', $vehicle->id)->update(['stock_number' => $code]);
        }

        // Now make the column non-nullable and add a unique index
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('stock_number', 5)->nullable(false)->unique()->change();
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
