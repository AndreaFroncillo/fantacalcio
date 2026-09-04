<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_transactions', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();

            $table->foreignId('team_credit_account_id')
                ->constrained('team_credit_accounts')
                ->restrictOnDelete();

            $table->string('type');

            $table->integer('amount');

            $table->unsignedInteger('balance_before');
            $table->unsignedInteger('balance_after');

            $table->string('description')->nullable();

            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_transactions');
    }
};
