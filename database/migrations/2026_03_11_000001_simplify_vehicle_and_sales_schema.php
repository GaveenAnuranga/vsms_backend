<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Make vehicles columns nullable (not required in simplified forms)
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('country_of_origin', 100)->nullable()->change();
            $table->enum('fuel_type', ['Gasoline', 'Diesel', 'Electric', 'Hybrid', 'Plug-in Hybrid'])->nullable()->change();
            $table->enum('transmission_type', ['Automatic', 'Manual', 'CVT', 'Semi-Automatic'])->nullable()->change();
            $table->string('color', 50)->nullable()->change();
        });

        // Make buyers columns nullable (buyer is now optional in sales)
        Schema::table('buyers', function (Blueprint $table) {
            $table->string('nic_or_reg', 100)->nullable()->change();
            $table->string('address', 255)->nullable()->change();
            $table->string('email', 150)->nullable()->change();
        });

        // Make sales columns nullable + add payment_description
        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('buyer_id')->nullable()->change();
            $table->foreignId('payment_method_id')->nullable()->change();
            $table->string('invoice_number', 100)->nullable()->change();
            $table->text('payment_description')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('payment_description');
            $table->string('invoice_number', 100)->nullable(false)->change();
            $table->foreignId('payment_method_id')->nullable(false)->change();
            $table->foreignId('buyer_id')->nullable(false)->change();
        });

        Schema::table('buyers', function (Blueprint $table) {
            $table->string('email', 150)->nullable(false)->change();
            $table->string('address', 255)->nullable(false)->change();
            $table->string('nic_or_reg', 100)->nullable(false)->change();
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('color', 50)->nullable(false)->change();
            $table->enum('transmission_type', ['Automatic', 'Manual', 'CVT', 'Semi-Automatic'])->nullable(false)->change();
            $table->enum('fuel_type', ['Gasoline', 'Diesel', 'Electric', 'Hybrid', 'Plug-in Hybrid'])->nullable(false)->change();
            $table->string('country_of_origin', 100)->nullable(false)->change();
        });
    }
};
