<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('market_sessions', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();

            $table->foreignId('league_season_id')
                ->constrained('league_seasons')
                ->restrictOnDelete();

            $table->string('name');
            $table->string('status');

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            $table->timestamps();

            $table->index(
                ['league_season_id', 'status'],
                'market_sessions_season_status_index'
            );

            $table->index(
                ['starts_at', 'ends_at'],
                'market_sessions_window_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_sessions');
    }
};
