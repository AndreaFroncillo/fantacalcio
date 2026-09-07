<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_releases', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();

            $table->foreignId('market_session_id')
                ->constrained('market_sessions')
                ->restrictOnDelete();

            $table->foreignId('roster_ownership_id')
                ->unique()
                ->constrained('roster_ownerships')
                ->restrictOnDelete();

            $table->foreignId('team_id')
                ->constrained('teams')
                ->restrictOnDelete();

            $table->foreignId('player_season_id')
                ->constrained('player_seasons')
                ->restrictOnDelete();

            $table->unsignedInteger('release_value');
            $table->timestamp('released_at');

            $table->timestamp('created_at')->nullable();

            $table->index(
                ['market_session_id', 'team_id'],
                'player_releases_session_team_index'
            );

            $table->index(
                ['market_session_id', 'player_season_id'],
                'player_releases_session_player_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_releases');
    }
};
