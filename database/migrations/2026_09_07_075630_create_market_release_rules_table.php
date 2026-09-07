<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('market_release_rules', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();

            $table->foreignId('market_session_id')
                ->constrained('market_sessions')
                ->restrictOnDelete();

            $table->unsignedSmallInteger('max_releases_per_team')->nullable();

            $table->timestamps();

            $table->unique(
                'market_session_id',
                'market_release_rules_market_session_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_release_rules');
    }
};
