<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCardPacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('card_packs', function (Blueprint $table) {
            $table->string('id', 30)->primary();
            $table->string('cycle_code', 30);
            $table->string('name');
            $table->integer('position');
            $table->integer('cycle_position')->nullable();
            $table->string('date_release', 10)->nullable();
            $table->boolean('usable');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('card_packs');
    }
}
