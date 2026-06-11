<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('review_laters', function (Blueprint $table) {
            $table->decimal('ease_factor', 5, 3)->default(2.500)->after('question_id');
            $table->unsignedInteger('interval_days')->default(1)->after('ease_factor');
            $table->unsignedInteger('repetitions')->default(0)->after('interval_days');
            $table->timestamp('next_review_at')->nullable()->after('repetitions');
            $table->unsignedTinyInteger('last_quality')->default(0)->after('next_review_at');

            $table->index(['user_id', 'next_review_at'], 'review_laters_user_due_idx');
        });
    }

    public function down(): void
    {
        Schema::table('review_laters', function (Blueprint $table) {
            $table->dropIndex('review_laters_user_due_idx');
            $table->dropColumn([
                'ease_factor',
                'interval_days',
                'repetitions',
                'next_review_at',
                'last_quality',
            ]);
        });
    }
};
