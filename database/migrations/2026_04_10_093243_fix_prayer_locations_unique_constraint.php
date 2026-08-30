<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prayer_locations', function (Blueprint $table) {
            $table->dropUnique(['province_id', 'city_id']);
            $table->unique(['equran_province', 'equran_city'], 'prayer_locations_equran_unique');
        });
    }

    public function down(): void
    {
        Schema::table('prayer_locations', function (Blueprint $table) {
            $table->dropUnique('prayer_locations_equran_unique');
            $table->unique(['province_id', 'city_id']);
        });
    }
};
