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
        Schema::create('user_info', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->nullable(false)->unsigned();
            $table->string('full_name', 255)->nullable(true)->comment('Full name');
            $table->string('avt_url', 255)->nullable(false)->comment('url avatar');
            $table->integer('sex')->nullable(false)->comment('sex')->comment('sex in 0,1,2');
            $table->string('address', 255)->nullable(true);
            $table->integer('language')->nullable(true)->comment('Languages in 1,2,3,4');
            $table->text('note')->nullable(false);
            $table->string('phone', 15)->nullable(false);
            $table->date('birth_day')->nullable(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_info');
    }
};
