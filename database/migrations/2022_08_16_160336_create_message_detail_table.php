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
        Schema::create('message_detail', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('message_id')->nullable(false);
            $table->bigInteger('user_id');
            $table->text('content')->nullable(false);
            $table->bigInteger('receiver_id');
            $table->string('url')->nullable();
            $table->integer('url_type');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('message_detail');
    }
};
