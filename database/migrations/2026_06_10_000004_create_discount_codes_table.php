<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discount_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('label')->nullable();
            $table->string('type', 20)->default('percentage');
            $table->decimal('value', 10, 2);
            $table->string('status', 20)->default('active');
            $table->decimal('min_amount', 10, 2)->default(0);
            $table->unsignedInteger('max_redemptions')->default(0);
            $table->unsignedInteger('current_redemptions')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('package_ids')->nullable();
            $table->json('course_ids')->nullable();
            $table->json('path_ids')->nullable();
            $table->json('subject_ids')->nullable();
            $table->json('content_types')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'expires_at']);
            $table->index(['created_by', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discount_codes');
    }
};
