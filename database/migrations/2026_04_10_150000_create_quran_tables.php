<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surahs', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('number')->unique();
            $table->string('name_arabic', 100);
            $table->string('name_latin', 100);
            $table->string('name_translation', 255)->nullable();
            $table->enum('revelation_type', ['meccan', 'medinan']);
            $table->unsignedSmallInteger('total_verses');
            $table->unsignedTinyInteger('juz_start')->default(1);
            $table->text('description_id')->nullable();
            $table->text('description_en')->nullable();
            $table->timestamps();

            $table->index('number');
        });

        Schema::create('quran_verses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surah_id')->constrained('surahs')->onDelete('cascade');
            $table->unsignedSmallInteger('verse_number');
            $table->unsignedTinyInteger('juz_number');
            $table->unsignedSmallInteger('page_number')->default(0);
            $table->text('text_uthmani');
            $table->text('text_tajweed')->nullable();
            $table->text('text_transliteration')->nullable();
            $table->text('translation_id');
            $table->text('translation_en')->nullable();
            $table->string('audio_url', 500)->nullable();
            $table->timestamps();

            $table->unique(['surah_id', 'verse_number']);
            $table->index('juz_number');
            $table->index('page_number');
        });

        Schema::create('quran_bookmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('surah_id')->constrained('surahs')->onDelete('cascade');
            $table->unsignedSmallInteger('verse_number');
            $table->string('label', 100)->nullable();
            $table->boolean('is_last_read')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'is_last_read']);
        });

        Schema::create('quran_khatam_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->unsignedSmallInteger('target_days')->default(30);
            $table->date('start_date');
            $table->date('target_date');
            $table->enum('status', ['active', 'completed', 'abandoned'])->default('active');
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        Schema::create('quran_khatam_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('quran_khatam_programs')->onDelete('cascade');
            $table->unsignedSmallInteger('surah_number');
            $table->unsignedSmallInteger('verse_from')->default(1);
            $table->unsignedSmallInteger('verse_to');
            $table->unsignedTinyInteger('juz_number');
            $table->unsignedSmallInteger('pages_read')->default(0);
            $table->date('read_date');
            $table->timestamps();

            $table->index(['program_id', 'read_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quran_khatam_progress');
        Schema::dropIfExists('quran_khatam_programs');
        Schema::dropIfExists('quran_bookmarks');
        Schema::dropIfExists('quran_verses');
        Schema::dropIfExists('surahs');
    }
};
