<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('market_capability_events', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();

            $table->foreignId('market_capability_id')
                ->constrained('market_capabilities')
                ->restrictOnDelete();

            $table->foreignId('performed_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('type');
            $table->text('reason')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->index(
                ['market_capability_id', 'created_at'],
                'market_capability_events_capability_created_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_capability_events');
    }
};
