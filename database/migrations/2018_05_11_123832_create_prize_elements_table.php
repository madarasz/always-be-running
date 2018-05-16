<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePrizeElementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prize_elements', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('prize_id')->unsigned();
            $table->string('quantity',15)->nullable();
            $table->string('title', 60)->nullable();;
            $table->string('type', 30);
            $table->integer('creator')->unsigned();
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
        Schema::drop('prize_elements');
    }
}
