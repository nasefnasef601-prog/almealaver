<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('access_code_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('access_code_id')->constrained('access_codes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('access_grant_id')->nullable()->constrained('access_grants')->nullOnDelete();
            $table->timestamp('redeemed_at');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['access_code_id', 'user_id']);
            $table->index(['user_id', 'redeemed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('access_code_redemptions');
    }
};
