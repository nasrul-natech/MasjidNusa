<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_adzan_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('prayer_locations')->nullOnDelete();

            $table->boolean('imsak_enabled')->default(false);
            $table->boolean('subuh_enabled')->default(true);
            $table->boolean('dzuhur_enabled')->default(true);
            $table->boolean('ashar_enabled')->default(true);
            $table->boolean('maghrib_enabled')->default(true);
            $table->boolean('isya_enabled')->default(true);

            $table->boolean('sound_enabled')->default(true);
            $table->string('sound_type', 50)->default('simple');
            $table->unsignedTinyInteger('volume')->default(80);

            $table->boolean('push_enabled')->default(true);
            $table->unsignedTinyInteger('minutes_before')->default(0);

            $table->timestamps();

            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_adzan_settings');
    }
};
