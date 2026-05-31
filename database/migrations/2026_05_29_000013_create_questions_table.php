<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->nullable()->constrained('quizzes')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->string('question_type', 15)->default('mcq');
            $table->text('question_text');
            $table->text('question_text_ar')->nullable();
            $table->text('options')->nullable();
            $table->text('correct_answer')->nullable();
            $table->text('explanation')->nullable();
            $table->text('explanation_ar')->nullable();
            $table->decimal('points', 5, 2)->default(1.00);
            $table->string('difficulty', 10)->default('medium');
            $table->foreignId('skill_id')->nullable()->constrained('skills')->nullOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('sections')->nullOnDelete();
            $table->string('status', 20)->default('draft');
            $table->timestamps();
            $table->softDeletes();

            $table->index('quiz_id');
            $table->index('created_by');
            $table->index('skill_id');
            $table->index('subject_id');
            $table->index('difficulty');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
