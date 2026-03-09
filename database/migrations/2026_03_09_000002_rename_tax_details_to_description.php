<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->renameColumn('tax_details', 'description');
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->renameColumn('tax_details', 'description');
        });

        if (Schema::hasTable('car_purchases')) {
            Schema::table('car_purchases', function (Blueprint $table) {
                $table->renameColumn('tax_details', 'description');
            });
        }
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->renameColumn('description', 'tax_details');
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->renameColumn('description', 'tax_details');
        });

        if (Schema::hasTable('car_purchases')) {
            Schema::table('car_purchases', function (Blueprint $table) {
                $table->renameColumn('description', 'tax_details');
            });
        }
    }
};
