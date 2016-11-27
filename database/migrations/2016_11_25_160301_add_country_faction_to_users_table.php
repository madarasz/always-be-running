<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCountryFactionToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('country_id')->unsigned()->default(0)->references('id')->on('countries');
            $table->string('favorite_faction', 20);
            $table->boolean('autofilter_upcoming')->default(false);
            $table->boolean('autofilter_results')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('country_id');
            $table->dropColumn('favorite_faction');
            $table->dropColumn('autofilter_upcoming');
            $table->dropColumn('autofilter_results');
        });
    }
}
