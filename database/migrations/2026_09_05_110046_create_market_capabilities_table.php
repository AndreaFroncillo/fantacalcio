<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('market_capabilities', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();

            $table->foreignId('market_session_id')
                ->constrained('market_sessions')
                ->restrictOnDelete();

            $table->string('type');
            $table->boolean('is_enabled')->default(false);

            $table->timestamps();

            $table->unique(
                ['market_session_id', 'type'],
                'market_capabilities_session_type_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_capabilities');
    }
};
