<?php

use App\Http\Controllers\Admin\MediaIT\MosqueDisplayController;
use App\Http\Controllers\Public\MosqueDisplayPublicController;
use Illuminate\Support\Facades\Route;

Route::middleware('new-feature:mosque_display')->group(function () {
    Route::get('/display-masjid', [MosqueDisplayPublicController::class, 'index'])->name('mosque-display.index');
    Route::get('/display-masjid/feed', [MosqueDisplayPublicController::class, 'feed'])->middleware('throttle:60,1')->name('mosque-display.feed');
    Route::get('/display-masjid/photos/{photo}', [MosqueDisplayPublicController::class, 'photo'])->where('photo', '[a-f0-9-]{36}\.jpg')->name('mosque-display.photo');
    Route::middleware(['auth', 'two-factor', 'role:admin_media_it,super_admin', 'permission:manage_mosque_display'])
        ->prefix('admin/media-it/mosque-display')->name('admin.media-it.mosque-display.')->group(function () {
            Route::get('/', [MosqueDisplayController::class, 'index'])->name('index');
            Route::put('/', [MosqueDisplayController::class, 'update'])->name('update');
            Route::post('/items', [MosqueDisplayController::class, 'storeItem'])->name('items.store');
            Route::put('/items/{item}', [MosqueDisplayController::class, 'updateItem'])->name('items.update');
            Route::delete('/items/{item}', [MosqueDisplayController::class, 'destroyItem'])->name('items.destroy');
            Route::post('/publish', [MosqueDisplayController::class, 'publish'])->name('publish');
            Route::get('/preview', [MosqueDisplayController::class, 'preview'])->name('preview');
            Route::get('/preview-feed', [MosqueDisplayController::class, 'previewFeed'])->name('preview-feed');
            Route::get('/photos/{photo}', [MosqueDisplayController::class, 'photo'])->where('photo', '[a-f0-9-]{36}\.jpg')->name('photo');
        });
});
