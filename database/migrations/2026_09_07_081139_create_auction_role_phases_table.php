<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auction_role_phases', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();

            $table->foreignId('auction_id')
                ->constrained('auctions')
                ->restrictOnDelete();

            $table->string('role');
            $table->unsignedTinyInteger('position');
            $table->string('status');

            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->unique(
                ['auction_id', 'role'],
                'auction_role_phases_auction_role_unique'
            );

            $table->unique(
                ['auction_id', 'position'],
                'auction_role_phases_auction_position_unique'
            );

            $table->index(
                ['auction_id', 'status'],
                'auction_role_phases_auction_status_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auction_role_phases');
    }
};
