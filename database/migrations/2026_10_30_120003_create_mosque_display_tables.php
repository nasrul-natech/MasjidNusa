<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mosque_displays', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->json('draft');
            $table->json('published')->nullable();
            $table->unsignedBigInteger('version')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
        Schema::create('mosque_display_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mosque_display_id')->constrained()->restrictOnDelete();
            $table->string('type', 10);
            $table->string('title', 160);
            $table->string('photo')->nullable();
            $table->string('youtube_id', 11)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedSmallInteger('duration_seconds')->default(15);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['mosque_display_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        throw new RuntimeException('Display data retained. Use a separately reviewed recovery procedure.');
    }
};
