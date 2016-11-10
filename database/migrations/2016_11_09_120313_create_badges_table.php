<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBadgesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('badges', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order');
            $table->string('name', 50);
            $table->string('description');
            $table->string('filename', 50);
            $table->boolean('auto')->default(1);
            $table->integer('year')->nullable();
            $table->tinyInteger('tournament_type_id')->nullable();
            $table->tinyInteger('winlevel')->nullable(); // 1=winner, 2=top, 5=participant
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('badges');
    }
}
