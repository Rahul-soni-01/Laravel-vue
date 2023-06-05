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
            $table->dropColumn('file_id');
            $table->string('photo')->nullable()->after('author_id');
            $table->string('avt')->nullable()->after('photo');
            $table->string('background')->nullable()->after('avt');
            $table->float('price')->default(0)->after('background');
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
            $table->bigInteger('file_id');
            $table->dropColumn('photo');
            $table->dropColumn('avt');
            $table->dropColumn('background');
            $table->dropColumn('price');
        });
    }
};
