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
        $this->seedBarcodes();
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

        $centralConnection = config('tenancy.database.central_connection', 'central');
        $currencies = \DB::connection($centralConnection)->table('currencies')->get()->map(fn($c) => (array) $c)->toArray();

        if (! empty($currencies)) {
            \DB::table('currencies')->insert($currencies);
        }
    }

    private function seedBarcodes(): void
    {
        if (\DB::table('barcodes')->exists()) {
            return;
        }

        $now = now()->toDateTimeString();

        \DB::table('barcodes')->insert([
            ['name' => '20 Labels per Sheet',       'description' => 'Sheet Size: 8.5" x 11", Label Size: 4" x 1", Labels per sheet: 20',          'width' => 4,     'height' => 1,    'paper_width' => 8.5, 'paper_height' => 11, 'top_margin' => 0.5,   'left_margin' => 0.125, 'row_distance' => 0,     'col_distance' => 0.1875, 'stickers_in_one_row' => 2, 'is_default' => 0, 'is_continuous' => 0, 'stickers_in_one_sheet' => 20, 'business_id' => null, 'created_at' => $now, 'updated_at' => $now],
            ['name' => '30 Labels per sheet',       'description' => 'Sheet Size: 8.5" x 11", Label Size: 2.625" x 1", Labels per sheet: 30',       'width' => 2.625, 'height' => 1,    'paper_width' => 8.5, 'paper_height' => 11, 'top_margin' => 0.5,   'left_margin' => 0.188, 'row_distance' => 0,     'col_distance' => 0.125,  'stickers_in_one_row' => 3, 'is_default' => 0, 'is_continuous' => 0, 'stickers_in_one_sheet' => 30, 'business_id' => null, 'created_at' => $now, 'updated_at' => $now],
            ['name' => '32 Labels per sheet',       'description' => 'Sheet Size: 8.5" x 11", Label Size: 2" x 1.25", Labels per sheet: 32',        'width' => 2,     'height' => 1.25, 'paper_width' => 8.5, 'paper_height' => 11, 'top_margin' => 0.5,   'left_margin' => 0.25,  'row_distance' => 0,     'col_distance' => 0,      'stickers_in_one_row' => 4, 'is_default' => 0, 'is_continuous' => 0, 'stickers_in_one_sheet' => 32, 'business_id' => null, 'created_at' => $now, 'updated_at' => $now],
            ['name' => '40 Labels per sheet',       'description' => 'Sheet Size: 8.5" x 11", Label Size: 2" x 1", Labels per sheet: 40',           'width' => 2,     'height' => 1,    'paper_width' => 8.5, 'paper_height' => 11, 'top_margin' => 0.5,   'left_margin' => 0.25,  'row_distance' => 0,     'col_distance' => 0,      'stickers_in_one_row' => 4, 'is_default' => 0, 'is_continuous' => 0, 'stickers_in_one_sheet' => 40, 'business_id' => null, 'created_at' => $now, 'updated_at' => $now],
            ['name' => '50 Labels per Sheet',       'description' => 'Sheet Size: 8.5" x 11", Label Size: 1.5" x 1", Labels per sheet: 50',         'width' => 1.5,   'height' => 1,    'paper_width' => 8.5, 'paper_height' => 11, 'top_margin' => 0.5,   'left_margin' => 0.5,   'row_distance' => 0,     'col_distance' => 0,      'stickers_in_one_row' => 5, 'is_default' => 0, 'is_continuous' => 0, 'stickers_in_one_sheet' => 50, 'business_id' => null, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Continuous Rolls - 31.75mm x 25.4mm', 'description' => 'Label Size: 31.75mm x 25.4mm, Gap: 3.18mm',                         'width' => 1.25,  'height' => 1,    'paper_width' => 1.25, 'paper_height' => 0,  'top_margin' => 0.125, 'left_margin' => 0,     'row_distance' => 0.125, 'col_distance' => 0,      'stickers_in_one_row' => 1, 'is_default' => 0, 'is_continuous' => 1, 'stickers_in_one_sheet' => null, 'business_id' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
