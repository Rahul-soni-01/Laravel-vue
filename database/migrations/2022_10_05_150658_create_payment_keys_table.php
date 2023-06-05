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
        Schema::create('payment_keys', function (Blueprint $table) {
            $table->id();
            $table->string('key')->nullable()->unique();
            $table->bigInteger('user_id');
            $table->tinyInteger('status')->default(0)->comment('0 or 1');
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
        Schema::dropIfExists('payment_keys');
    }
};
