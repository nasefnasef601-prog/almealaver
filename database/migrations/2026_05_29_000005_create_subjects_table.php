<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('path_id')->constrained('paths')->cascadeOnDelete();
            $table->string('name');
            $table->string('name_ar');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->string('icon')->nullable();
            $table->string('color', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['path_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
