<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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
        if (!Schema::hasColumn('sales', 'tax_amount')) {
            DB::statement("ALTER TABLE `sales` ADD COLUMN `tax_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER `sale_price`");
        }

        if (!Schema::hasColumn('sales', 'branch')) {
            DB::statement("ALTER TABLE `sales` ADD COLUMN `branch` VARCHAR(150) NULL AFTER `tax_amount`");
        }

        if (!Schema::hasColumn('sales', 'document_path')) {
            DB::statement("ALTER TABLE `sales` ADD COLUMN `document_path` VARCHAR(255) NULL AFTER `branch`");
        }

        if (!Schema::hasColumn('sales', 'tax_details')) {
            DB::statement("ALTER TABLE `sales` ADD COLUMN `tax_details` TEXT NULL AFTER `document_path`");
        }

        // Make seller nic/email nullable and ensure seller_type has a default
        if (Schema::hasTable('sellers')) {
            if (Schema::hasColumn('sellers', 'nic_or_reg')) {
                DB::statement("ALTER TABLE `sellers` MODIFY `nic_or_reg` VARCHAR(100) NULL");
            }

            if (Schema::hasColumn('sellers', 'email')) {
                DB::statement("ALTER TABLE `sellers` MODIFY `email` VARCHAR(150) NULL");
            }

            if (Schema::hasColumn('sellers', 'seller_type')) {
                DB::statement("ALTER TABLE `sellers` MODIFY `seller_type` VARCHAR(100) NOT NULL DEFAULT 'individual'");
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('sales', 'tax_amount')) {
            DB::statement("ALTER TABLE `sales` DROP COLUMN `tax_amount`");
        }

        if (Schema::hasColumn('sales', 'branch')) {
            DB::statement("ALTER TABLE `sales` DROP COLUMN `branch`");
        }

        if (Schema::hasColumn('sales', 'document_path')) {
            DB::statement("ALTER TABLE `sales` DROP COLUMN `document_path`");
        }

        if (Schema::hasColumn('sales', 'tax_details')) {
            DB::statement("ALTER TABLE `sales` DROP COLUMN `tax_details`");
        }

        if (Schema::hasTable('sellers')) {
            if (Schema::hasColumn('sellers', 'nic_or_reg')) {
                DB::statement("ALTER TABLE `sellers` MODIFY `nic_or_reg` VARCHAR(100) NOT NULL");
            }

            if (Schema::hasColumn('sellers', 'email')) {
                DB::statement("ALTER TABLE `sellers` MODIFY `email` VARCHAR(150) NOT NULL");
            }

            if (Schema::hasColumn('sellers', 'seller_type')) {
                DB::statement("ALTER TABLE `sellers` MODIFY `seller_type` VARCHAR(100) NOT NULL");
            }
        }
    }
}
