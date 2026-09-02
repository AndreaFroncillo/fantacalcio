<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_seasons', function (Blueprint $table) {
            $table->id();

            $table->ulid('ulid')->unique();

            $table->foreignId('football_season_id')
                ->constrained('football_seasons')
                ->restrictOnDelete();

            $table->foreignId('football_player_id')
                ->constrained('football_players')
                ->restrictOnDelete();

            $table->foreignId('real_club_id')
                ->constrained('real_clubs')
                ->restrictOnDelete();

            $table->string('role');
            $table->string('status')->default('active');

            $table->timestamps();

            $table->unique(
                ['football_season_id', 'football_player_id'],
                'player_seasons_season_player_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_seasons');
    }
};
