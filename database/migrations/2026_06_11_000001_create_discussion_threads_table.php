<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discussion_threads', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type', 20)->default('course');
            $table->unsignedBigInteger('entity_id');
            $table->foreignId('course_id')->nullable()->constrained('courses')->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->string('title', 180);
            $table->text('body');
            $table->json('upvoter_ids')->nullable();
            $table->unsignedInteger('upvotes_count')->default(0);
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_resolved')->default(false);
            $table->unsignedInteger('replies_count')->default(0);
            $table->timestamps();

            $table->index(['entity_type', 'entity_id', 'is_pinned', 'created_at'], 'discussion_entity_sort_idx');
            $table->index(['course_id', 'is_resolved']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discussion_threads');
    }
};
