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
        Schema::table('fan_user', function (Blueprint $table) {
            $table->dropColumn('price_pay');
            $table->dropColumn('month');
            $table->dropColumn('payment_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fan_user', function (Blueprint $table) {
            $table->float('price_pay')->nullable()->after('user_id');
            $table->integer('month')->nullable()->after('price_pay');
            $table->date('payment_date')->nullable()->after('month');
        });
    }
};
