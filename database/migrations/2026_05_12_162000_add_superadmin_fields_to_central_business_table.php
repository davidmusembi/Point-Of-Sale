<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('business', function (Blueprint $table) {
            if (! Schema::hasColumn('business', 'created_by')) {
                $table->integer('created_by')->nullable();
            }
            if (! Schema::hasColumn('business', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
        });
    }

    public function down(): void
    {
        Schema::table('business', function (Blueprint $table) {
            $table->dropColumn(array_filter(['created_by', 'is_active'], fn($c) => Schema::hasColumn('business', $c)));
        });
    }
};
