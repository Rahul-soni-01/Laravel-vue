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
        Schema::table('user_info', function (Blueprint $table) {
            $table->string('avt_url')->nullable()->change();
            $table->string('front_photo')->nullable()->change();
            $table->string('backside_photo')->nullable()->change();
            $table->string('note')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_info', function (Blueprint $table) {
            $table->string('avt_url')->nullable(false)->change();
            $table->string('front_photo')->nullable(false)->change();
            $table->string('backside_photo')->nullable(false)->change();
            $table->string('note')->nullable(false)->change();
        });
    }
};
