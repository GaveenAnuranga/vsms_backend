<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateSalesAndSellersAddFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add missing columns to sales
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'tax_amount')) {
                $table->decimal('tax_amount', 12, 2)->default(0.00);
            }
            if (!Schema::hasColumn('sales', 'branch')) {
                $table->string('branch', 150)->nullable();
            }
            if (!Schema::hasColumn('sales', 'document_path')) {
                $table->string('document_path', 255)->nullable();
            }
            if (!Schema::hasColumn('sales', 'tax_details')) {
                $table->text('tax_details')->nullable();
            }
        });

        // Make seller nic/email nullable and ensure seller_type has a default
        if (Schema::hasTable('sellers')) {
            Schema::table('sellers', function (Blueprint $table) {
                if (Schema::hasColumn('sellers', 'nic_or_reg')) {
                    $table->string('nic_or_reg', 100)->nullable()->change();
                }
                if (Schema::hasColumn('sellers', 'email')) {
                    $table->string('email', 150)->nullable()->change();
                }
                if (Schema::hasColumn('sellers', 'seller_type')) {
                    $table->string('seller_type', 100)->default('individual')->change();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            $columns = ['tax_amount', 'branch', 'document_path', 'tax_details'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('sales', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        if (Schema::hasTable('sellers')) {
            Schema::table('sellers', function (Blueprint $table) {
                if (Schema::hasColumn('sellers', 'nic_or_reg')) {
                    $table->string('nic_or_reg', 100)->nullable(false)->change();
                }
                if (Schema::hasColumn('sellers', 'email')) {
                    $table->string('email', 150)->nullable(false)->change();
                }
                if (Schema::hasColumn('sellers', 'seller_type')) {
                    $table->string('seller_type', 100)->default(null)->change();
                }
            });
        }
    }
}
