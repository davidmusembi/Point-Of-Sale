<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class TenantSeeder extends Seeder
{
    /**
     * Seeds essential data into a freshly provisioned tenant database.
     * Called by SeedTenantData job after MigrateDatabase completes.
     */
    public function run()
    {
        $this->seedPermissions();
        $this->seedCurrencies();
    }

    private function seedPermissions(): void
    {
        // Core permissions expected by the default roles and UI.
        // Module-specific permissions are added via tenant migrations.
        $names = [
            'user.view', 'user.create', 'user.update', 'user.delete',
            'supplier.view', 'supplier.create', 'supplier.update', 'supplier.delete',
            'customer.view', 'customer.create', 'customer.update', 'customer.delete',
            'product.view', 'product.create', 'product.update', 'product.delete',
            'purchase.view', 'purchase.create', 'purchase.update', 'purchase.delete',
            'sell.view', 'sell.create', 'sell.update', 'sell.delete',
            'purchase_n_sell_report.view', 'contacts_report.view', 'stock_report.view',
            'tax_report.view', 'trending_product_report.view', 'register_report.view',
            'sales_representative.view', 'expense_report.view',
            'business_settings.access', 'barcode_settings.access', 'invoice_settings.access',
            'brand.view', 'brand.create', 'brand.update', 'brand.delete',
            'tax_rate.view', 'tax_rate.create', 'tax_rate.update', 'tax_rate.delete',
            'unit.view', 'unit.create', 'unit.update', 'unit.delete',
            'category.view', 'category.create', 'category.update', 'category.delete',
            'expense.access',
            'access_all_locations',
            'dashboard.data',
        ];

        $now = Carbon::now()->toDateTimeString();

        $existing = Permission::pluck('name')->flip();

        $insert = [];
        foreach ($names as $name) {
            if (! isset($existing[$name])) {
                $insert[] = ['name' => $name, 'guard_name' => 'web', 'created_at' => $now, 'updated_at' => $now];
            }
        }

        if (! empty($insert)) {
            Permission::insert($insert);
        }
    }

    private function seedCurrencies(): void
    {
        if (\DB::table('currencies')->exists()) {
            return;
        }

        // Copy currency data from the central database.
        $centralConnection = config('tenancy.database.central_connection', 'central');
        $currencies = \DB::connection($centralConnection)->table('currencies')->get()->map(fn($c) => (array) $c)->toArray();

        if (! empty($currencies)) {
            \DB::table('currencies')->insert($currencies);
        }
    }
}
