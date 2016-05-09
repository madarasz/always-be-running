<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEntriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entries', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('rank')->unsigned();
            $table->integer('rank_top')->unsigned();
            $table->string('deck_title');
            $table->integer('deck_id')->unsigned();
            $table->string('deck_version');
            $table->boolean('approved')->nullable();
            $table->integer('user');   // TODO: foreign key for user
            $table->integer('tournament_id')->unsigned();
            $table->foreign('tournament_id')->references('id')->on('tournaments');
            $table->timestamps();
            // TODO: faction, agenda
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('entries');
    }
}
