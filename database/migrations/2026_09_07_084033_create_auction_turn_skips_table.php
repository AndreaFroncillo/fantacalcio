<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auction_turn_skips', function (Blueprint $table) {
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

            $table->unsignedInteger('turn_number');

            $table->string('reason');

            $table->foreignId('performed_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('note')->nullable();

            $table->timestamp('created_at')->nullable();

            $table->unique(
                ['auction_id', 'turn_number'],
                'auction_turn_skips_auction_turn_unique'
            );

            $table->index(
                ['auction_role_phase_id', 'auction_participant_id'],
                'auction_turn_skips_phase_participant_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auction_turn_skips');
    }
};
