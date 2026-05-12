<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;

class AddRecipeAddEditPermissions extends Migration
{
    public function up()
    {
        Permission::create(['name' => 'manufacturing.add_recipe']);
        Permission::create(['name' => 'manufacturing.edit_recipe']);
    }

    public function down()
    {
    }
}
