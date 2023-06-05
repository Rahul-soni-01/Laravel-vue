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
            $table->dropColumn('object_type');
            $table->dropColumn('object_id');
            $table->bigInteger('product_id')->after('user_id')->unsigned()->nullable();
            $table->bigInteger('post_id')->after('product_id')->unsigned()->nullable();
            $table->bigInteger('fan_id')->after('post_id')->unsigned()->nullable();
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
            $table->bigInteger('object_id')->after('user_id')->unsigned();
            $table->tinyInteger('object_type')->after('type');
            $table->dropColumn('product_id');
            $table->dropColumn('post_id');
            $table->dropColumn('fan_id');
        });
    }
};
