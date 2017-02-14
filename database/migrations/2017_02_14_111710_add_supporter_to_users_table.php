<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSupporterToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->tinyInteger('supporter')->unsigned()->default(0);
            // 0 - not a supporter
            // 1 - card / one-time supporter
            // 2 - patreon bioroid supporter
            // 3 - patreon sysop supporter
            // 4 - patreon executive supporter
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
            $table->dropColumn('supporter');
        });
    }
}
