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
        Schema::table('files', function (Blueprint $table) {
            $table->bigInteger('user_id')->after('url')->unsigned()->nullable(false);
            $table->integer('user_file_type')->after('user_id')->nullable(false);
        });

        Schema::table('files', function (Blueprint $table) {
            $table->foreign('user_id')->on('users')->references('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('files', function (Blueprint $table) {
            $table->dropColumn('user_id');
            $table->dropColumn('user_file_type');
        });
    }
};
