<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTournamentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tournaments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('date');
            $table->string('start_time');
            $table->integer('location_country')->unsigned();
            $table->integer('location_us_state')->unsigned();
            $table->string('location_city');
            $table->string('location_store');
            $table->string('location_address');
            $table->integer('players_number')->unsigned()->nullable();
            $table->integer('top_number')->unsigned()->nullable();
            $table->text('description');
            $table->boolean('concluded');
            $table->boolean('decklist');
            $table->integer('creator')->unsigned();
            $table->boolean('approved')->nullable();
            //$table->string('reject_reason'); do I need this?
            $table->string('cardpool_id');
            $table->integer('tournament_type_id')->unsigned();
            $table->foreign('tournament_type_id')->references('id')->on('tournament_types');
            $table->foreign('creator')->references('id')->on('users');
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
        Schema::drop('tournaments');
    }
}
