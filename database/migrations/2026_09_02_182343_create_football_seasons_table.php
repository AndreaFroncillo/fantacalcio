<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('football_seasons', function (Blueprint $table) {
            $table->id();

            $table->ulid('ulid')->unique();

            $table->string('name');

            $table->unsignedSmallInteger('start_year');
            $table->unsignedSmallInteger('end_year');

            $table->string('status')->default('draft');

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            $table->timestamps();

            $table->unique(
                ['start_year', 'end_year'],
                'football_seasons_years_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('football_seasons');
    }
};
