<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auctions', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();

            $table->foreignId('market_session_id')
                ->constrained('market_sessions')
                ->restrictOnDelete();

            $table->string('status');

            $table->unsignedSmallInteger('base_timer_seconds');
            $table->unsignedSmallInteger('bid_extension_seconds');

            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->unique(
                'market_session_id',
                'auctions_market_session_unique'
            );

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auctions');
    }
};
