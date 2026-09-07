<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auction_participants', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();

            $table->foreignId('auction_id')
                ->constrained('auctions')
                ->restrictOnDelete();

            $table->foreignId('team_id')
                ->constrained('teams')
                ->restrictOnDelete();

            $table->unsignedSmallInteger('nomination_position');

            $table->timestamps();

            $table->unique(
                ['auction_id', 'team_id'],
                'auction_participants_auction_team_unique'
            );

            $table->unique(
                ['auction_id', 'nomination_position'],
                'auction_participants_auction_position_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auction_participants');
    }
};
