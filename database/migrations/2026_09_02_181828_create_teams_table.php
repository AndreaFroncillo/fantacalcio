<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();

            $table->ulid('ulid')->unique();

            $table->foreignId('season_participation_id')
                ->constrained('season_participations')
                ->restrictOnDelete();

            $table->string('name');

            $table->string('short_name')->nullable();

            $table->string('logo_path')->nullable();

            $table->string('status')->default('active');

            $table->timestamps();

            $table->unique(
                'season_participation_id',
                'teams_season_participation_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
