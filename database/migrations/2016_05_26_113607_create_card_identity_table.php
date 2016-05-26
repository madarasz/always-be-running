<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCardIdentityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('card_identities', function (Blueprint $table) {
            $table->string('id', 5)->index();
            $table->string('pack_code', 10);
            $table->string('faction_code', 12);
            $table->boolean('runner');
            $table->string('title');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('card_identities');
    }
}
