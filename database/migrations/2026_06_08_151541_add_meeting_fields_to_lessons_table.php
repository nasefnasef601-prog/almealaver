<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->timestamp('meeting_date')->nullable();
            $table->string('meeting_url', 2048)->nullable();
            $table->string('recording_url', 2048)->nullable();
            $table->text('join_instructions')->nullable();
            $table->boolean('show_recording_on_platform')->default(false);
            $table->boolean('show_on_platform')->default(true);
            $table->text('allowed_groups')->nullable(); // JSON arrays or comma-separated group IDs
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn([
                'meeting_date',
                'meeting_url',
                'recording_url',
                'join_instructions',
                'show_recording_on_platform',
                'show_on_platform',
                'allowed_groups',
            ]);
        });
    }
};
