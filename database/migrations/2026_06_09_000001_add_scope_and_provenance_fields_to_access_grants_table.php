<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('access_grants', function (Blueprint $table) {
            $table->json('course_ids')->nullable()->after('course_id');
            $table->json('content_types')->nullable()->after('package_id');
            $table->json('path_ids')->nullable()->after('content_types');
            $table->json('subject_ids')->nullable()->after('path_ids');
            $table->string('source_type', 40)->nullable()->after('status');
            $table->string('source_id')->nullable()->after('source_type');
            $table->string('idempotency_key')->nullable()->after('source_id');
            $table->json('metadata')->nullable()->after('idempotency_key');
            $table->timestamp('granted_at')->nullable()->after('payment_request_id');
            $table->foreignId('revoked_by')->nullable()->after('expires_at')->constrained('users')->nullOnDelete();
            $table->timestamp('revoked_at')->nullable()->after('revoked_by');
            $table->text('revoke_reason')->nullable()->after('revoked_at');
        });

        Schema::table('access_grants', function (Blueprint $table) {
            $table->index('source_type');
            $table->index('source_id');
            $table->unique('idempotency_key');
        });
    }

    public function down(): void
    {
        Schema::table('access_grants', function (Blueprint $table) {
            $table->dropUnique(['idempotency_key']);
            $table->dropIndex(['source_type']);
            $table->dropIndex(['source_id']);
            $table->dropConstrainedForeignId('revoked_by');
            $table->dropColumn([
                'course_ids',
                'content_types',
                'path_ids',
                'subject_ids',
                'source_type',
                'source_id',
                'idempotency_key',
                'metadata',
                'granted_at',
                'revoked_at',
                'revoke_reason',
            ]);
        });
    }
};
