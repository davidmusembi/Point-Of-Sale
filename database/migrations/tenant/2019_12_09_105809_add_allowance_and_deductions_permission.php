<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;

class AddAllowanceAndDeductionsPermission extends Migration
{
    public function up()
    {
        Permission::create(['name' => 'essentials.add_allowance_and_deduction']);
    }

    public function down()
    {
    }
}
