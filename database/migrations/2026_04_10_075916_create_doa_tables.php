<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doa_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon', 50)->default('book');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('duas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doa_category_id')->constrained('doa_categories')->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('arabic')->nullable();
            $table->text('latin')->nullable();
            $table->text('translation');
            $table->longText('content_html')->nullable();
            $table->text('content_text')->nullable();
            $table->string('source')->nullable();
            $table->enum('status', ['draft', 'publish'])->default('draft');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('user_doa_favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('doa_id')->constrained('duas')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['user_id', 'doa_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_doa_favorites');
        Schema::dropIfExists('duas');
        Schema::dropIfExists('doa_categories');
    }
};
