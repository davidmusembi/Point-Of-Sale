<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->decimal('storage_limit', 22, 4)->default(0)->after('business_count')->comment('Storage limit in MB, 0 = infinite');
            $table->text('enabled_modules')->nullable()->after('storage_limit')->comment('JSON array of modules allowed in this package');
        });
    }

    public function down()
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn(['storage_limit', 'enabled_modules']);
        });
    }
};
