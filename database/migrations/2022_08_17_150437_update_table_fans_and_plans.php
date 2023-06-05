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
        Schema::table('fans', function (Blueprint $table) {
            $table->text('sub_title')->after('title')->nullable();
        });

        Schema::table('fans', function ($table) {
            $table->dropColumn('price');
        });

        Schema::table('plans', function ($table) {
            $table->text('sub_title')->after('title')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fans', function (Blueprint $table) {
            $table->dropColumn('sub_title');
        });

        Schema::table('fans', function ($table) {
            $table->integer('price');
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('sub_title');
        });
    }
};
