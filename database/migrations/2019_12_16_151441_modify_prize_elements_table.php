<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyPrizeElementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('prize_elements', function (Blueprint $table) {
            $table->integer('prize_id')->unsigned()->nullable()->change();
            $table->string('type', 30)->nullable()->change();
            $table->integer('artist_id')->unsigned()->nullable();
            $table->boolean('official')->default(true);
            $table->boolean('proper')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('prize_elements', function (Blueprint $table) {
            $table->integer('prize_id')->unsigned()->change();
            $table->string('type', 30)->change();
            $table->dropColumn('artist_id');
            $table->dropColumn('official');
            $table->dropColumn('proper');
        });
    }
}
