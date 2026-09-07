<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auction_bids', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();

            $table->foreignId('auction_nomination_id')
                ->constrained('auction_nominations')
                ->restrictOnDelete();

            $table->foreignId('auction_participant_id')
                ->constrained('auction_participants')
                ->restrictOnDelete();

            $table->unsignedInteger('amount');
            $table->unsignedInteger('sequence_number');

            $table->timestamp('placed_at');

            $table->timestamp('created_at')->nullable();

            $table->unique(
                ['auction_nomination_id', 'sequence_number'],
                'auction_bids_nomination_sequence_unique'
            );

            $table->index(
                ['auction_nomination_id', 'amount'],
                'auction_bids_nomination_amount_index'
            );

            $table->index('auction_participant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auction_bids');
    }
};
