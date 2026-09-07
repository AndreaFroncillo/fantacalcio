<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auction_nominations', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();

            $table->foreignId('auction_id')
                ->constrained('auctions')
                ->restrictOnDelete();

            $table->foreignId('auction_role_phase_id')
                ->constrained('auction_role_phases')
                ->restrictOnDelete();

            $table->foreignId('auction_participant_id')
                ->constrained('auction_participants')
                ->restrictOnDelete();

            $table->foreignId('player_season_id')
                ->constrained('player_seasons')
                ->restrictOnDelete();

            $table->unsignedInteger('turn_number');

            $table->string('status');

            $table->unsignedInteger('opening_price');

            $table->timestamp('timer_started_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('closed_at')->nullable();

            $table->foreignId('closed_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('close_reason')->nullable();

            $table->timestamps();

            $table->unique(
                ['auction_id', 'turn_number'],
                'auction_nominations_auction_turn_unique'
            );

            $table->index(
                ['auction_id', 'status'],
                'auction_nominations_auction_status_index'
            );

            $table->index('player_season_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auction_nominations');
    }
};
