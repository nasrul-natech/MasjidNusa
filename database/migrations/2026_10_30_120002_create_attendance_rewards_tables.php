<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_reward_periods', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->date('start_date');
            $table->date('end_date');
            $table->string('timezone', 64);
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('status', 16)->default('draft');
            $table->unsignedTinyInteger('winner_count');
            $table->string('rules_version', 32)->default('attendance-v1');
            $table->dateTime('effective_cutoff_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('finalized_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->dateTime('finalized_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'starts_at', 'ends_at'], 'reward_period_range_index');
        });

        Schema::create('attendance_reward_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->constrained('attendance_reward_periods')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->unsignedTinyInteger('rank');
            $table->string('display_name');
            $table->unsignedInteger('points');
            $table->dateTime('reached_at');
            $table->string('reward_status', 24)->default('pending');
            $table->foreignId('status_changed_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->dateTime('status_changed_at')->nullable();
            $table->foreignId('delivered_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->dateTime('delivered_at')->nullable();
            $table->timestamps();
            $table->unique(['period_id', 'user_id']);
            $table->unique(['period_id', 'rank']);
        });
    }

    public function down(): void
    {
        throw new RuntimeException('Snapshot hadiah dipertahankan. Nonaktifkan modul; jangan hapus riwayat hadiah.');
    }
};
