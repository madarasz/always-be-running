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
        Schema::table('tournaments', function (Blueprint $table) {
            $table->index(['incomplete', 'concluded', 'date'], 'tournaments_incomplete_concluded_date_idx');
            $table->index(['incomplete', 'approved', 'date'], 'tournaments_incomplete_approved_date_idx');
            $table->index(['deleted_at', 'date'], 'tournaments_deleted_at_date_idx');
        });

        Schema::table('entries', function (Blueprint $table) {
            $table->index(['tournament_id', 'user'], 'entries_tournament_user_idx');
            $table->index(['user', 'tournament_id', 'rank'], 'entries_user_tournament_rank_idx');
            $table->index(['tournament_id', 'runner_deck_id'], 'entries_tournament_runner_deck_idx');
            $table->index(['tournament_id', 'rank_top', 'rank'], 'entries_tournament_rank_top_rank_idx');
        });

        Schema::table('photos', function (Blueprint $table) {
            $table->index(['tournament_id'], 'photos_tournament_id_idx');
        });

        Schema::table('videos', function (Blueprint $table) {
            $table->index(['flag_removed', 'tournament_id'], 'videos_flag_removed_tournament_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->dropIndex('videos_flag_removed_tournament_idx');
        });

        Schema::table('photos', function (Blueprint $table) {
            $table->dropIndex('photos_tournament_id_idx');
        });

        Schema::table('entries', function (Blueprint $table) {
            $table->dropIndex('entries_tournament_user_idx');
            $table->dropIndex('entries_user_tournament_rank_idx');
            $table->dropIndex('entries_tournament_runner_deck_idx');
            $table->dropIndex('entries_tournament_rank_top_rank_idx');
        });

        Schema::table('tournaments', function (Blueprint $table) {
            $table->dropIndex('tournaments_incomplete_concluded_date_idx');
            $table->dropIndex('tournaments_incomplete_approved_date_idx');
            $table->dropIndex('tournaments_deleted_at_date_idx');
        });
    }
};
