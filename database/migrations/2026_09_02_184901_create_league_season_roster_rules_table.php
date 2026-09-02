<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('league_season_roster_rules', function (Blueprint $table) {
            $table->id();

            $table->ulid('ulid')->unique();

            $table->foreignId('league_season_id')
                ->constrained('league_seasons')
                ->restrictOnDelete();

            $table->string('role');

            $table->unsignedSmallInteger('max_players');

            $table->timestamps();

            $table->unique(
                ['league_season_id', 'role'],
                'league_season_roster_rules_season_role_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('league_season_roster_rules');
    }
};
