<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('DELETE bu1 FROM badge_user bu1 INNER JOIN badge_user bu2 ON bu1.user_id = bu2.user_id AND bu1.badge_id = bu2.badge_id AND bu1.id > bu2.id');

        Schema::table('badge_user', function (Blueprint $table) {
            $table->dropIndex('badge_user_user_badge_idx');
            $table->unique(['user_id', 'badge_id'], 'badge_user_user_badge_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('badge_user', function (Blueprint $table) {
            $table->dropUnique('badge_user_user_badge_unique');
            $table->index(['user_id', 'badge_id'], 'badge_user_user_badge_idx');
        });
    }
};
