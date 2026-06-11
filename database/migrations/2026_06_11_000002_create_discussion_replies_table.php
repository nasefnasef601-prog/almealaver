<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discussion_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discussion_thread_id')->constrained('discussion_threads')->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->json('upvoter_ids')->nullable();
            $table->unsignedInteger('upvotes_count')->default(0);
            $table->boolean('is_instructor_reply')->default(false);
            $table->boolean('is_accepted_answer')->default(false);
            $table->timestamps();

            $table->index(['discussion_thread_id', 'created_at'], 'discussion_reply_thread_sort_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discussion_replies');
    }
};
