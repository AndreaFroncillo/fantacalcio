<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('league_invitations', function (Blueprint $table) {
            $table->id();

            $table->ulid('ulid')->unique();

            $table->foreignId('league_id')
                ->constrained('leagues')
                ->restrictOnDelete();

            $table->foreignId('invited_by_user_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('email');

            $table->string('token')->unique();

            $table->string('status')->default('pending');

            $table->timestamp('expires_at');

            $table->timestamp('accepted_at')->nullable();

            $table->timestamp('revoked_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('league_invitations');
    }
};
