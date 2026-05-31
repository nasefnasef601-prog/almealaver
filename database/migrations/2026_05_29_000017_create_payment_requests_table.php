<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('payment_method');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('SAR');
            $table->string('status', 25)->default('pending_manual_review');
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->string('bank_transfer_receipt', 2048)->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('metadata')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
            $table->index('payment_method');
            $table->index('created_at');
            $table->index(['status', 'payment_method']);
            $table->index('admin_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_requests');
    }
};
