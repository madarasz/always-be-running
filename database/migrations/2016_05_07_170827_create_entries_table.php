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
            $table->integer('rank')->unsigned()->nullable();
            $table->integer('rank_top')->unsigned()->nullable();
            $table->string('runner_deck_title');
            $table->integer('runner_deck_id')->unsigned()->nullable();
            $table->string('runner_deck_identity', 5);
            $table->string('corp_deck_title');
            $table->integer('corp_deck_id')->unsigned()->nullable();
            $table->string('corp_deck_identity', 5);
            $table->boolean('approved')->nullable();
            $table->integer('user')->unsigned()->nullable();
            $table->integer('tournament_id')->unsigned();
            $table->foreign('tournament_id')->references('id')->on('tournaments');
            $table->timestamps();
            $table->unique(['user', 'id']);
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
