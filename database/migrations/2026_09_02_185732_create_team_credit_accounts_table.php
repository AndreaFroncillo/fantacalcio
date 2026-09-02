<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_credit_accounts', function (Blueprint $table) {
            $table->id();

            $table->ulid('ulid')->unique();

            $table->foreignId('team_id')
                ->constrained('teams')
                ->restrictOnDelete();

            $table->unsignedInteger('initial_balance');
            $table->unsignedInteger('current_balance');

            $table->timestamps();

            $table->unique(
                'team_id',
                'team_credit_accounts_team_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_credit_accounts');
    }
};
