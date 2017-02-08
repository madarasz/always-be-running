<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNrdbClaimToEntriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('entries', function (Blueprint $table) {
            $table->integer('netrunnerdb_claim_runner')->unsigned()->nullable();
            $table->integer('netrunnerdb_claim_corp')->unsigned()->nullable();
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
            $table->dropColumn('netrunnerdb_claim_runner');
            $table->dropColumn('netrunnerdb_claim_corp');
        });
    }
}
