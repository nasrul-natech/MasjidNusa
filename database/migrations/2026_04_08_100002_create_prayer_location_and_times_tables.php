<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prayer_locations', function (Blueprint $table) {
            $table->id();
            $table->string('province_id', 10);
            $table->string('province_name', 100);
            $table->string('city_id', 10);
            $table->string('city_name', 100);
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->unique(['province_id', 'city_id']);
        });

        Schema::create('prayer_times', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained('prayer_locations')->onDelete('cascade');
            $table->date('date');
            $table->time('imsak')->nullable();
            $table->time('subuh');
            $table->time('terbit')->nullable();
            $table->time('dhuha')->nullable();
            $table->time('dzuhur');
            $table->time('ashar');
            $table->time('maghrib');
            $table->time('isya');
            $table->timestamps();

            $table->unique(['location_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prayer_times');
        Schema::dropIfExists('prayer_locations');
    }
};
