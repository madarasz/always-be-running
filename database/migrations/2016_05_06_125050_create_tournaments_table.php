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
            $table->dateTime('date');
            $table->string('location_country');
            $table->string('location_city');
            $table->string('location_store');
            $table->integer('players_number')->unsigned();
            $table->text('description');
            $table->boolean('top');
            $table->integer('creator');   // TODO: foreign key for user
            $table->boolean('approved')->nullable();
            $table->integer('tournament_type_id')->unsigned();
            $table->foreign('tournament_type_id')->references('id')->on('tournament_types');
            $table->timestamps();
//             TODO: cardpool, location
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
