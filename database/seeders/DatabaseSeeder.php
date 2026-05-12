<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    // BarcodesTableSeeder and PermissionsTableSeeder excluded — those tables are
    // tenant-only. They are seeded via TenantSeeder when each tenant is provisioned.

    public function run()
    {
        $this->call([
            CurrenciesTableSeeder::class,
            SuperadminUserSeeder::class,
            PackageSeeder::class,
        ]);
    }
}
