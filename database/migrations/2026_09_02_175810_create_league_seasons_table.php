<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('league_seasons', function (Blueprint $table) {
            $table->id();

            $table->ulid('ulid')->unique();

            $table->foreignId('league_id')
                ->constrained('leagues')
                ->restrictOnDelete();

            $table->unsignedSmallInteger('start_year');

            $table->unsignedSmallInteger('end_year');

            $table->string('status')->default('draft');

            $table->char('currency', 3);

            $table->unsignedSmallInteger('max_teams');

            $table->unsignedInteger('initial_credits');

            $table->timestamp('starts_at')->nullable();

            $table->timestamp('ends_at')->nullable();

            $table->timestamps();

            $table->unique([
                'league_id',
                'start_year',
                'end_year',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('league_seasons');
    }
};
