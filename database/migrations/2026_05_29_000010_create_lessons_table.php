<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('course_modules')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->string('title');
            $table->string('title_ar');
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->string('content_type', 20)->default('video');
            $table->string('content_url', 2048)->nullable();
            $table->longText('content_text')->nullable();
            $table->longText('content_text_ar')->nullable();
            $table->string('video_provider', 50)->nullable();
            $table->string('video_url', 2048)->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->boolean('is_free')->default(false);
            $table->boolean('is_published')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('module_id');
            $table->index('course_id');
            $table->index('is_published');
            $table->index(['module_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
