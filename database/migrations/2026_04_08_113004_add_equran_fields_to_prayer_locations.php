<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prayer_locations', function (Blueprint $table) {
            $table->string('equran_province')->nullable()->after('province_name');
            $table->string('equran_city')->nullable()->after('city_name');
        });
    }

    public function down(): void
    {
        Schema::table('prayer_locations', function (Blueprint $table) {
            $table->dropColumn(['equran_province', 'equran_city']);
        });
    }
};
