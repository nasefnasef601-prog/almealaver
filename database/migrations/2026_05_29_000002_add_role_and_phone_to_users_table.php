<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable();
            $table->string('role', 20)->default('student');
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->text('linked_student_ids')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('profile_photo_path', 2048)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'role',
                'school_id',
                'linked_student_ids',
                'is_active',
                'profile_photo_path',
            ]);
        });
    }
};
