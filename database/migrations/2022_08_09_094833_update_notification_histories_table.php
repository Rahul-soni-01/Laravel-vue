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
            $table->bigInteger('created_by')->after('user_id')->nullable(false)->unsigned();
            $table->tinyInteger('type')->after('content')->nullable(false);
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
            $table->dropColumn('created_by');
            $table->dropColumn('type');
        });
    }
};
