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
            $table->renameColumn('branch_id', 'brand_id');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('branch_id', 'brand_id');
        });
        Schema::table('posts', function (Blueprint $table) {
            $table->renameColumn('branch_id', 'brand_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('brand', function (Blueprint $table) {
            //
        });
    }
};
