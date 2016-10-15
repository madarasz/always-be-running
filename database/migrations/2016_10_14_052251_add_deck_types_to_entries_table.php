<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeckTypesToEntriesTable extends Migration
{
    /**
     * Run the migrations.
     * Adding types for runner and corp decks.
     * Type:
     * 1 - NetrunnerDB published deck
     * 2 - NetrunnerDB private deck
     * @return void
     */
    public function up()
    {
        Schema::table('entries', function (Blueprint $table) {
            $table->smallInteger('runner_deck_type')->nullable();
            $table->smallInteger('corp_deck_type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('entries', function (Blueprint $table) {
            $table->dropColumn('runner_deck_type');
            $table->dropColumn('corp_deck_type');
        });
    }
}
