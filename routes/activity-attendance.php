<?php

use App\Http\Controllers\Admin\MediaIT\ActivityAdminController;
use App\Http\Controllers\Admin\Operasional\ActivityAttendanceController as OperationalAttendance;
use App\Http\Controllers\User\ActivityAttendanceController;
use App\Http\Controllers\User\JamaahAttendanceLeaderboardController;
use Illuminate\Support\Facades\Route;

Route::middleware('new-feature:activity_attendance')->group(function () {
    Route::get('/api/kegiatan/attendance-phases', [ActivityAttendanceController::class, 'phases'])->middleware('throttle:60,1')->name('attendance.phases');
    Route::post('/admin/media-it/activities/{activity}/complete', [ActivityAdminController::class, 'complete'])
        ->middleware(['auth', 'two-factor', 'role:admin_media_it,super_admin', 'permission:manage_activities', 'throttle:20,1'])
        ->name('admin.media-it.activities.complete');
    Route::get('/api/kegiatan/{activity}/attendance', [ActivityAttendanceController::class, 'state'])->name('attendance.state');
    Route::post('/user/kegiatan/{activity}/check-in', [ActivityAttendanceController::class, 'store'])->middleware(['auth', 'throttle:20,1'])->name('attendance.store');
    Route::get('/user/kehadiran', [ActivityAttendanceController::class, 'history'])->middleware('auth')->name('user.attendance');
    Route::get('/user/kehadiran/peringkat', JamaahAttendanceLeaderboardController::class)->middleware('auth')->name('user.attendance.leaderboard');
    Route::middleware(['auth', 'two-factor', 'role:admin_operasional,super_admin', 'permission:manage_activity_attendance'])
        ->prefix('admin/operasional/attendance')->name('admin.operasional.attendance.')->group(function () {
            Route::get('/', [OperationalAttendance::class, 'index'])->name('index');
            Route::post('/{activity}/open', [OperationalAttendance::class, 'open'])->middleware('throttle:20,1')->name('open');
        });
});
