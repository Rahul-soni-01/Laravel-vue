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
        Schema::table('plans', function (Blueprint $table) {
            $table->string('pro_stripe_id', 255)->nullable(true)->after('price');
            $table->string('price_stripe_id', 255)->nullable(true)->after('pro_stripe_id');
            $table->json('product_stripe')->nullable(true)->after('price_stripe_id');
            $table->json('price_stripe')->nullable(true)->after('product_stripe');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('pro_stripe_id');
            $table->dropColumn('price_stripe_id');
            $table->dropColumn('product_stripe');
            $table->dropColumn('price_stripe');
        });
    }
};
