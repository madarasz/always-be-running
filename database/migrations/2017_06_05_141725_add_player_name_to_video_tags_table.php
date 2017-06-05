<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPlayerNameToVideoTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('video_tags', function (Blueprint $table) {
            $table->string('import_player_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('video_tags', function (Blueprint $table) {
            $table->dropColumn('import_player_name');
        });
    }
}
