<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_attendance_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('enabled')->default(false);
            $table->unsignedInteger('duration_seconds')->default(120);
            $table->timestamps();
        });
        Schema::create('activity_attendance_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->unique()->constrained()->restrictOnDelete();
            $table->timestamp('completed_at');
            $table->timestamp('opened_at')->index();
            $table->timestamp('closes_at')->index();
            $table->unsignedInteger('duration_seconds');
            $table->foreignId('opened_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
        Schema::create('activity_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('activity_attendance_sessions')->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->timestamp('checked_in_at')->index();
            $table->unique(['session_id', 'user_id']);
            $table->index(['user_id', 'checked_in_at']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        throw new RuntimeException('Tabel kehadiran dipertahankan. Nonaktifkan modul; jangan hapus bukti kehadiran.');
    }
};
