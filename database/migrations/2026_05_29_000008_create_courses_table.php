<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('title_ar');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->string('short_description')->nullable();
            $table->string('thumbnail', 2048)->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->boolean('is_free')->default(false);
            $table->boolean('is_published')->default(false);
            $table->string('status', 20)->default('draft');
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->foreignId('skill_id')->nullable()->constrained('skills')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('assigned_teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('difficulty_level', 20)->default('all');
            $table->integer('duration_minutes')->nullable();
            $table->text('prerequisites')->nullable();
            $table->boolean('has_certificate')->default(false);
            $table->integer('sort_order')->default(0);
            $table->text('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('subject_id');
            $table->index('skill_id');
            $table->index('created_by');
            $table->index('is_published');
            $table->index('status');
            $table->index('price');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
