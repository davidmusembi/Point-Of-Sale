<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $table_name = config('activitylog.table_name', 'activity_log');

        if (Schema::hasTable($table_name)) {
            return;
        }

        Schema::create($table_name, function (Blueprint $table) {
            $table->increments('id');
            $table->string('log_name')->nullable();
            $table->text('description');
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('causer_id')->nullable();
            $table->string('causer_type')->nullable();
            $table->json('properties')->nullable();
            $table->string('event')->nullable();
            $table->uuid('batch_uuid')->nullable();
            $table->integer('business_id')->nullable();
            $table->timestamps();

            $table->index('log_name');
        });
    }

    public function down()
    {
        Schema::dropIfExists(config('activitylog.table_name', 'activity_log'));
    }
};
