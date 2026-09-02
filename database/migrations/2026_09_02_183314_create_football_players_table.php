<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('football_players', function (Blueprint $table) {
            $table->id();

            $table->ulid('ulid')->unique();

            $table->string('first_name');
            $table->string('last_name');
            $table->string('display_name');

            $table->date('date_of_birth')->nullable();
            $table->string('nationality')->nullable();
            $table->string('photo_path')->nullable();

            $table->string('status')->default('active');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('football_players');
    }
};
