<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $methods = [
            'Cash',
            'Bank Transfer',
            'Cheque',
            'Credit Card',
            'Debit Card',
            'Installment / Finance',
            'Lease',
        ];

        foreach ($methods as $name) {
            DB::table('payment_methods')->insertOrIgnore(['name' => $name]);
        }

        $this->command->info('Payment methods seeded successfully!');
    }
}
