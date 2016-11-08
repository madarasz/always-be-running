<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUsernamesToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username_real')->nullable();
            $table->string('username_preferred')->nullable();
            $table->string('username_jinteki')->nullable();
            $table->string('username_stimhack')->nullable();
            $table->string('username_twitter')->nullable();
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
            $table->dropColumn('username_real');
            $table->dropColumn('username_preferred');
            $table->dropColumn('username_jinteki');
            $table->dropColumn('username_stimhack');
            $table->dropColumn('username_twitter');
        });
    }
}
