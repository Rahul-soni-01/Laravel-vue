<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notification_histories', function (Blueprint $table) {
            $table->bigInteger('object_id')->after('user_id')->unsigned();
            $table->tinyInteger('object_type')->after('type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notification_histories', function (Blueprint $table) {
            $table->dropColumn('object_id');
            $table->dropColumn('object_type');
        });
    }
};
