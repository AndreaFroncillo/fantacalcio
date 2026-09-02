<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('leagues', function (Blueprint $table) {
            $table->id();

            $table->ulid('ulid')->unique();

            $table->string('name');

            $table->text('description')->nullable();

            $table->string('join_code')
                ->nullable()
                ->unique();

            $table->string('timezone');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leagues');
    }
};
