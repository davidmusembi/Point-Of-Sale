<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    // PermissionsTableSeeder excluded — permissions table is tenant-only.
    // Permissions are seeded via TenantSeeder when each tenant is provisioned.

    public function run()
    {
        $this->call([
            BarcodesTableSeeder::class,
            CurrenciesTableSeeder::class,
            SuperadminUserSeeder::class,
            PackageSeeder::class,
        ]);
    }
}
