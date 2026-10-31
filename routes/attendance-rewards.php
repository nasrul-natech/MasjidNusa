<?php

use App\Http\Controllers\Admin\Operasional\AttendanceRewardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'two-factor', 'role:admin_operasional,super_admin', 'new-feature:attendance_rewards'])
    ->prefix('admin/operasional/attendance-rewards')->name('admin.operasional.attendance-rewards.')
    ->group(function () {
        Route::get('/', [AttendanceRewardController::class, 'index'])->middleware('permission:view_attendance_rewards')->name('index');
        Route::post('/', [AttendanceRewardController::class, 'store'])->middleware('permission:manage_attendance_rewards')->name('store');
        Route::get('/{period}', [AttendanceRewardController::class, 'show'])->middleware('permission:view_attendance_rewards')->name('show');
        Route::post('/{period}/activate', [AttendanceRewardController::class, 'activate'])->middleware('permission:manage_attendance_rewards')->name('activate');
        Route::post('/{period}/finalize', [AttendanceRewardController::class, 'finalize'])->middleware(['permission:finalize_attendance_rewards', 'throttle:10,1'])->name('finalize');
        Route::patch('/{period}/recipients/{recipient}', [AttendanceRewardController::class, 'updateStatus'])->middleware('permission:manage_attendance_rewards')->name('recipients.update');
    });
