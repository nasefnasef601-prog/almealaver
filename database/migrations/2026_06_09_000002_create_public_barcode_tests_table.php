<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_barcode_tests', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('path_id')->nullable()->constrained('paths')->nullOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('sections')->nullOnDelete();
            $table->json('skill_ids')->nullable();
            $table->json('question_ids');
            $table->string('test_kind', 20)->default('quick');
            $table->string('status', 20)->default('draft');
            $table->boolean('show_result_to_student')->default(true);
            $table->boolean('collect_school')->default(true);
            $table->boolean('collect_classroom')->default(true);
            $table->json('settings')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->unsignedInteger('max_submissions')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('owner_type', 20)->default('platform');
            $table->string('owner_id')->nullable();
            $table->timestamps();

            $table->index(['status', 'starts_at', 'ends_at']);
            $table->index(['path_id', 'subject_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public_barcode_tests');
    }
};
