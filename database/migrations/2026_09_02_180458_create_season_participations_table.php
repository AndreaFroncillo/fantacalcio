<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('season_participations', function (Blueprint $table) {
            $table->id();

            $table->ulid('ulid')->unique();

            $table->foreignId('league_season_id')
                ->constrained('league_seasons')
                ->restrictOnDelete();

            $table->foreignId('league_membership_id')
                ->constrained('league_memberships')
                ->restrictOnDelete();

            $table->string('status')->default('active');

            $table->timestamp('joined_at');

            $table->timestamps();

            $table->unique(
                ['league_season_id', 'league_membership_id'],
                'season_participations_season_membership_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('season_participations');
    }
};
