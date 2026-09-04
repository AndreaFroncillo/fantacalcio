<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roster_ownerships', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();

            $table->foreignId('league_season_id')
                ->constrained('league_seasons')
                ->restrictOnDelete();

            $table->foreignId('team_id')
                ->constrained('teams')
                ->restrictOnDelete();

            $table->foreignId('player_season_id')
                ->constrained('player_seasons')
                ->restrictOnDelete();

            $table->unsignedInteger('acquisition_value');

            $table->timestamp('acquired_at');
            $table->timestamp('released_at')->nullable();

            $table->timestamps();

            $table->index(
                ['league_season_id', 'player_season_id'],
                'roster_ownerships_season_player_index'
            );

            $table->index(
                ['team_id', 'released_at'],
                'roster_ownerships_team_active_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roster_ownerships');
    }
};
