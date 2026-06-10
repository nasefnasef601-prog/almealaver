<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_barcode_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('public_barcode_test_id')->constrained('public_barcode_tests')->cascadeOnDelete();
            $table->string('student_name');
            $table->string('school_name')->nullable();
            $table->string('classroom')->nullable();
            $table->json('answers')->nullable();
            $table->decimal('score', 8, 2)->default(0);
            $table->decimal('total_points', 8, 2)->default(0);
            $table->decimal('score_percentage', 5, 2)->default(0);
            $table->boolean('passed')->default(false);
            $table->unsignedInteger('correct_count')->default(0);
            $table->unsignedInteger('incorrect_count')->default(0);
            $table->unsignedInteger('unanswered_count')->default(0);
            $table->json('review')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['public_barcode_test_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public_barcode_submissions');
    }
};
