<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('library_items')) {
            return;
        }

        Schema::create('library_items', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('size')->nullable();
            $table->unsignedInteger('downloads')->default(0);
            $table->string('type', 20)->default('pdf');
            $table->foreignId('path_id')->constrained('paths')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('sections')->nullOnDelete();
            $table->json('skill_ids')->nullable();
            $table->string('url', 2048)->nullable();
            $table->boolean('show_on_platform')->default(true);
            $table->boolean('is_locked')->default(false);
            $table->string('owner_type', 20)->default('platform');
            $table->string('owner_id')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('approval_status', 30)->default('draft');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('reviewer_notes')->nullable();
            $table->decimal('revenue_share_percentage', 5, 2)->nullable();
            $table->timestamps();

            $table->index(['path_id', 'subject_id', 'section_id', 'show_on_platform'], 'lib_items_scope_show_idx');
            $table->index(['owner_type', 'owner_id', 'approval_status'], 'lib_items_owner_status_idx');
            $table->index(['assigned_teacher_id', 'approval_status'], 'lib_items_teacher_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_items');
    }
};
