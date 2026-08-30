<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('color', 7)->default('#1B6B3A');
            $table->timestamps();
        });

        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->longText('content')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('activity_categories')->onDelete('set null');
            $table->string('location')->nullable();
            $table->datetime('start_date');
            $table->datetime('end_date')->nullable();
            $table->string('banner')->nullable();
            $table->enum('status', ['draft', 'publish', 'selesai', 'arsip'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->boolean('show_popup')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('activity_galleries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained()->onDelete('cascade');
            $table->string('image');
            $table->string('caption')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('activity_stories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('media');
            $table->enum('media_type', ['image', 'video'])->default('image');
            $table->boolean('is_highlight')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('popup_ads', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('image');
            $table->string('link')->nullable();
            $table->foreignId('activity_id')->nullable()->constrained()->onDelete('set null');
            $table->boolean('is_active')->default(true);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('popup_ads');
        Schema::dropIfExists('activity_stories');
        Schema::dropIfExists('activity_galleries');
        Schema::dropIfExists('activities');
        Schema::dropIfExists('activity_categories');
    }
};
