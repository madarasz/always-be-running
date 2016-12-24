<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDataToVideos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->integer('user_id')->unsigned()->references('id')->on('users');
            $table->string('thumbnail_url');
            $table->string('channel_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('videos', function (Blueprint $table) {
          $table->dropColumn('user_id');
          $table->dropColumn('thumbnail_url');
          $table->dropColumn('channel_name');
        });
    }
}
