<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('b2b_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->string('name');
            $table->foreignId('assigned_teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('revenue_share_percentage', 5, 2)->nullable();
            $table->json('course_ids')->nullable();
            $table->json('content_types')->nullable();
            $table->json('path_ids')->nullable();
            $table->json('subject_ids')->nullable();
            $table->string('type', 30)->default('free_access');
            $table->unsignedTinyInteger('discount_percentage')->nullable();
            $table->unsignedInteger('max_students')->default(0);
            $table->string('status', 20)->default('active');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'status']);
            $table->index(['assigned_teacher_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('b2b_packages');
    }
};
