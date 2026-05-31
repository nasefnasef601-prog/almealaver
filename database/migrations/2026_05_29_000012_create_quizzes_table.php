<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('title_ar');
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->foreignId('course_id')->nullable()->constrained('courses')->nullOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('sections')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->string('quiz_type', 20)->default('practice');
            $table->string('difficulty', 10)->default('medium');
            $table->integer('time_limit')->nullable();
            $table->decimal('passing_score', 5, 2)->default(50.00);
            $table->integer('max_attempts')->default(0);
            $table->boolean('randomize_questions')->default(false);
            $table->boolean('show_answers')->default(true);
            $table->boolean('show_explanations')->default(true);
            $table->boolean('is_published')->default(false);
            $table->string('status', 20)->default('draft');
            $table->timestamps();
            $table->softDeletes();

            $table->index('course_id');
            $table->index('created_by');
            $table->index('is_published');
            $table->index('status');
            $table->index('difficulty');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
