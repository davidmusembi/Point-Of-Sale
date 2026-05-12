<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;

class AddManufacturingPermissions extends Migration
{
    public function up()
    {
        Permission::create(['name' => 'manufacturing.access_recipe']);
        Permission::create(['name' => 'manufacturing.access_production']);
    }

    public function down()
    {
    }
}
