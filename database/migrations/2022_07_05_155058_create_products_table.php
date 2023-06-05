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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255)->nullable(false);
            $table->text('content')->nullable(true);
            $table->integer('file_id')->nullable(false);
            $table->integer('category_id')->nullable(false);
            $table->integer('author_id')->nullable(true);
            $table->integer('tag_id')->nullable(false);
            $table->integer('price')->nullable(false);
            $table->integer('like')->nullable(false);
            $table->integer('type')->default(1);
            $table->integer('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
};
