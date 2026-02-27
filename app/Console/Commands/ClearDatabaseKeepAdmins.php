<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClearDatabaseKeepAdmins extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:clear-keep-admins {--force : Force the operation without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all database data but keep admin accounts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('This will delete all data except admin accounts. Do you want to continue?')) {
                $this->info('Operation cancelled.');
                return;
            }
        }

        $this->info('Starting database cleanup...');

        try {
            // Get all table names
            $tables = $this->getTables();

            // Tables to truncate completely before users
            $tablesToTruncateFirst = [
                'vehicle_images',
                'vehicle_imports',
                'vehicle_registrations',
                'transfers',
                'sales',
                'purchases',
                'purchase_sellers',
                'vehicles',
                'buyers',
                'sellers',
                'dealers',
            ];

            // Truncate tables with foreign key dependencies first
            // Note: TRUNCATE implicitly commits in MySQL, so we can't wrap it in a transaction
            Schema::disableForeignKeyConstraints();

            foreach ($tablesToTruncateFirst as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->truncate();
                    $this->info("✓ Cleared table: {$table}");
                }
            }

            // Clear tenants
            if (Schema::hasTable('tenants')) {
                DB::table('tenants')->truncate();
                $this->info("✓ Cleared table: tenants");
            }

            // Delete non-admin users (keep only users with admin roles)
            if (Schema::hasTable('users')) {
                $deletedCount = DB::table('users')
                    ->whereNotIn('role', ['platform_admin', 'company_admin', 'dealer_admin'])
                    ->delete();
                
                // Set tenant_id to null for all remaining admin users
                DB::table('users')->update(['tenant_id' => null]);
                
                $adminCount = DB::table('users')->count();
                $this->info("✓ Kept {$adminCount} admin accounts, deleted {$deletedCount} non-admin users");
            }

            Schema::enableForeignKeyConstraints();

            $this->info('');
            $this->info('Database cleanup completed successfully!');
            $this->info('Admin accounts have been preserved.');
            
        } catch (\Exception $e) {
            Schema::enableForeignKeyConstraints();
            
            $this->error('Error during database cleanup: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Get all table names from the database
     *
     * @return array
     */
    private function getTables(): array
    {
        $tables = [];
        $results = DB::select('SHOW TABLES');
        $databaseName = DB::getDatabaseName();
        $key = 'Tables_in_' . $databaseName;

        foreach ($results as $result) {
            $tables[] = $result->$key;
        }

        return $tables;
    }
}
