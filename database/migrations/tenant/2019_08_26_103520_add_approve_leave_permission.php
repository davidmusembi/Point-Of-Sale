<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;

class AddApproveLeavePermission extends Migration
{
    public function up()
    {
        Permission::create(['name' => 'essentials.approve_leave']);
    }

    public function down()
    {
    }
}
