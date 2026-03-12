<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('entries', function (Blueprint $table) {
            $table->index(['user', 'type', 'tournament_id'], 'entries_user_type_tournament_idx');
            $table->index(['user', 'type', 'runner_deck_identity'], 'entries_user_type_runner_identity_idx');
            $table->index(['user', 'type', 'corp_deck_identity'], 'entries_user_type_corp_identity_idx');
        });

        Schema::table('tournaments', function (Blueprint $table) {
            $table->index(['creator', 'approved', 'import'], 'tournaments_creator_approved_import_idx');
            $table->index(['creator', 'approved', 'tournament_type_id', 'concluded'], 'tournaments_creator_approved_type_concluded_idx');
        });

        Schema::table('badges', function (Blueprint $table) {
            $table->index(['tournament_type_id', 'year', 'winlevel'], 'badges_type_year_winlevel_idx');
        });

        Schema::table('badge_user', function (Blueprint $table) {
            $table->index(['user_id'], 'badge_user_user_id_idx');
            $table->index(['user_id', 'badge_id'], 'badge_user_user_badge_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('badge_user', function (Blueprint $table) {
            $table->dropIndex('badge_user_user_badge_idx');
            $table->dropIndex('badge_user_user_id_idx');
        });

        Schema::table('badges', function (Blueprint $table) {
            $table->dropIndex('badges_type_year_winlevel_idx');
        });

        Schema::table('tournaments', function (Blueprint $table) {
            $table->dropIndex('tournaments_creator_approved_import_idx');
            $table->dropIndex('tournaments_creator_approved_type_concluded_idx');
        });

        Schema::table('entries', function (Blueprint $table) {
            $table->dropIndex('entries_user_type_tournament_idx');
            $table->dropIndex('entries_user_type_runner_identity_idx');
            $table->dropIndex('entries_user_type_corp_identity_idx');
        });
    }
};
