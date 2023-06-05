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
        Schema::table('posts', function (Blueprint $table) {
            $table->longText('content')->change();
        });
        Schema::table('fans', function (Blueprint $table) {
            $table->longText('content')->change();
        });
        Schema::table('products', function (Blueprint $table) {
            $table->longText('content')->change();
        });
        Schema::table('livestreams', function (Blueprint $table) {
            $table->longText('content')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->text('content')->change();
        });
        Schema::table('fans', function (Blueprint $table) {
            $table->text('content')->change();
        });
        Schema::table('products', function (Blueprint $table) {
            $table->text('content')->change();
        });
        Schema::table('livestreams', function (Blueprint $table) {
            $table->text('content')->change();
        });
    }
};
