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
        Schema::table('plan_user', function (Blueprint $table) {
            $table->dateTime('expired_date')->nullable(true)->after('status')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('plan_user', function (Blueprint $table) {
            $table->text('expired_date')->nullable(true)->after('status')->change();
        });
    }
};
