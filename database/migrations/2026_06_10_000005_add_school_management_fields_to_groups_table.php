<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->json('course_ids')->nullable()->after('owner_id');
            $table->json('settings')->nullable()->after('metadata');
            $table->text('description')->nullable()->after('name');
            $table->string('location')->nullable()->after('description');
            $table->boolean('is_active')->default(true)->after('type');

            $table->index(['school_id', 'type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropIndex(['school_id', 'type', 'is_active']);
            $table->dropColumn(['course_ids', 'settings', 'description', 'location', 'is_active']);
        });
    }
};
