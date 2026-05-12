<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;

class AddMessagePermissions extends Migration
{
    public function up()
    {
        Permission::create(['name' => 'essentials.create_message']);
        Permission::create(['name' => 'essentials.view_message']);
    }

    public function down()
    {
    }
}
