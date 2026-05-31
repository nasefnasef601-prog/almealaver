<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skill_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->decimal('mastery', 5, 1)->default(0)->comment('0-100%');
            $table->string('status')->default('weak')->comment('weak/average/good/mastered');
            $table->integer('total_attempts')->default(0);
            $table->integer('correct_answers')->default(0);
            $table->integer('total_questions')->default(0);
            $table->foreignId('last_quiz_id')->nullable()->constrained('quizzes')->nullOnDelete();
            $table->string('last_quiz_title')->nullable();
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'skill_id']);
            $table->index(['user_id', 'status', 'mastery']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skill_progress');
    }
};
