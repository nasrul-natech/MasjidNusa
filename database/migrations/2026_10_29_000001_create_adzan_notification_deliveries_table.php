<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adzan_notification_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('location_id');
            $table->string('prayer', 20);
            $table->date('sent_on');
            $table->timestamps();

            $table->unique(['user_id', 'location_id', 'prayer', 'sent_on'], 'adzan_delivery_once_per_day');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adzan_notification_deliveries');
    }
};
