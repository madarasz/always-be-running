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
        Schema::table('badge_user', function (Blueprint $table) {
            $table->index(['badge_id', 'user_id'], 'badge_user_badge_user_idx');
        });

        Schema::table('video_tags', function (Blueprint $table) {
            $table->index(['user_id'], 'video_tags_user_id_idx');
        });

        Schema::table('photos', function (Blueprint $table) {
            $table->index(['user_id'], 'photos_user_id_idx');
        });

        Schema::table('videos', function (Blueprint $table) {
            $table->index(['user_id'], 'videos_user_id_idx');
        });

        Schema::table('card_packs', function (Blueprint $table) {
            $table->index(['cycle_code', 'position'], 'card_packs_cycle_position_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('card_packs', function (Blueprint $table) {
            $table->dropIndex('card_packs_cycle_position_idx');
        });

        Schema::table('videos', function (Blueprint $table) {
            $table->dropIndex('videos_user_id_idx');
        });

        Schema::table('photos', function (Blueprint $table) {
            $table->dropIndex('photos_user_id_idx');
        });

        Schema::table('video_tags', function (Blueprint $table) {
            $table->dropIndex('video_tags_user_id_idx');
        });

        Schema::table('badge_user', function (Blueprint $table) {
            $table->dropIndex('badge_user_badge_user_idx');
        });
    }
};
