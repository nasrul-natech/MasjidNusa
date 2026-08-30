<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mosque_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('address')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->text('description')->nullable();
            $table->text('vision')->nullable();
            $table->text('mission')->nullable();
            $table->string('photo')->nullable();
            $table->string('banner')->nullable();
            $table->string('latitude', 20)->nullable();
            $table->string('longitude', 20)->nullable();
            $table->string('google_maps_url')->nullable();
            $table->string('website')->nullable();
            $table->json('social_media')->nullable();
            $table->timestamps();
        });

        Schema::create('mosque_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mosque_profile_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->longText('content');
            $table->date('event_date')->nullable();
            $table->json('documentation')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('management_histories', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->year('start_year');
            $table->year('end_year')->nullable();
            $table->boolean('is_current')->default(false);
            $table->string('photo')->nullable();
            $table->timestamps();
        });

        Schema::create('organization_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('management_history_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('position');
            $table->string('photo')->nullable();
            $table->foreignId('parent_id')->nullable()->references('id')->on('organization_structures')->onDelete('set null');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_structures');
        Schema::dropIfExists('management_histories');
        Schema::dropIfExists('mosque_histories');
        Schema::dropIfExists('mosque_profiles');
    }
};
