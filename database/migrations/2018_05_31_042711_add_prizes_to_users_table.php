<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPrizesToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('prize_owning_public')->default(false);
            $table->boolean('prize_trading_public')->default(false);
            $table->boolean('prize_wanting_public')->default(false);
            $table->text('prize_owning_text')->nullable();
            $table->text('prize_trading_text')->nullable();
            $table->text('prize_wanting_text')->nullable();
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
            //
        });
    }
}
