<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBrokenToEntriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('entries', function (Blueprint $table) {
            $table->boolean('broken_runner')->default(false);
            $table->boolean('broken_corp')->default(false);
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
            $table->dropColumn('broken_runner');
            $table->dropColumn('broken_corp');
        });
    }
}
