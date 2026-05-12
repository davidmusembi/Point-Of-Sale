<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    public function run()
    {
        // Skip if a package already exists (idempotent).
        if (\DB::table('packages')->exists()) {
            return;
        }

        \DB::table('packages')->insert([
            [
                'name'           => 'Starter',
                'description'    => 'Default starter package. Edit or replace via Superadmin → Packages.',
                'location_count' => 1,
                'user_count'     => 5,
                'product_count'  => 0,   // 0 = unlimited
                'invoice_count'  => 0,
                'business_count' => 1,
                'storage_limit'  => 0,
                'interval'       => 'months',
                'interval_count' => 1,
                'trial_days'     => 30,
                'price'          => 0.0000,
                'created_by'     => 1,
                'sort_order'     => 1,
                'is_active'      => 1,
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
        ]);
    }
}
