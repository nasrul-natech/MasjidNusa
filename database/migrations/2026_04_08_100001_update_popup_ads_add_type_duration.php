<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('popup_ads', function (Blueprint $table) {
            $table->enum('ad_type', ['image', 'link'])->default('image')->after('title');
            $table->text('content')->nullable()->after('link');
            $table->unsignedInteger('duration')->default(5)->after('end_date');
            $table->unsignedInteger('priority')->default(0)->after('duration');
        });
    }

    public function down(): void
    {
        Schema::table('popup_ads', function (Blueprint $table) {
            $table->dropColumn(['ad_type', 'content', 'duration', 'priority']);
        });
    }
};
