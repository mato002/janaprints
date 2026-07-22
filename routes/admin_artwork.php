<?php

use App\Http\Controllers\Admin\Artwork\ArtworkCommentController;
use App\Http\Controllers\Admin\Artwork\ArtworkDashboardController;
use App\Http\Controllers\Admin\Artwork\ArtworkFileController;
use App\Http\Controllers\Admin\Artwork\ArtworkRequestController;
use App\Http\Controllers\Admin\Artwork\ArtworkVersionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant'])
    ->prefix('admin/artwork')
    ->name('admin.artwork.')
    ->group(function () {
        Route::get('/', ArtworkDashboardController::class)
            ->middleware('permission:artwork.view')
            ->name('dashboard');

        Route::middleware('permission:artwork.view')->group(function () {
            Route::get('requests', [ArtworkRequestController::class, 'index'])->name('index');
        });

        Route::middleware('permission:artwork.create')->group(function () {
            Route::get('requests/create', [ArtworkRequestController::class, 'create'])->name('create');
            Route::post('requests', [ArtworkRequestController::class, 'store'])->name('store');
        });

        Route::middleware('permission:artwork.view')->group(function () {
            Route::get('requests/{artworkRequest}', [ArtworkRequestController::class, 'show'])->name('show');
            Route::get('requests/{artworkRequest}/versions/{version}/preview', [ArtworkVersionController::class, 'preview'])->name('versions.preview');
            Route::get('requests/{artworkRequest}/versions/{version}/download', [ArtworkVersionController::class, 'download'])->name('versions.download');
            Route::get('requests/{artworkRequest}/files/{file}/download', [ArtworkFileController::class, 'download'])->name('files.download');
        });

        Route::middleware('permission:artwork.edit')->group(function () {
            Route::get('requests/{artworkRequest}/edit', [ArtworkRequestController::class, 'edit'])->name('edit');
            Route::put('requests/{artworkRequest}', [ArtworkRequestController::class, 'update'])->name('update');
            Route::post('requests/{artworkRequest}/start-design', [ArtworkRequestController::class, 'startDesign'])->name('start-design');
            Route::post('requests/{artworkRequest}/versions', [ArtworkVersionController::class, 'store'])->name('versions.store');
            Route::post('requests/{artworkRequest}/files', [ArtworkFileController::class, 'store'])->name('files.store');
            Route::delete('requests/{artworkRequest}/files/{file}', [ArtworkFileController::class, 'destroy'])->name('files.destroy');
            Route::post('requests/{artworkRequest}/comments', [ArtworkCommentController::class, 'store'])->name('comments.store');
        });

        Route::middleware('permission:artwork.delete')->group(function () {
            Route::delete('requests/{artworkRequest}', [ArtworkRequestController::class, 'destroy'])->name('destroy');
        });

        Route::middleware('permission:artwork.assign')->group(function () {
            Route::post('requests/{artworkRequest}/assign', [ArtworkRequestController::class, 'assign'])->name('assign');
        });

        Route::middleware('permission:artwork.submit')->group(function () {
            Route::post('requests/{artworkRequest}/submit', [ArtworkRequestController::class, 'submit'])->name('submit');
        });

        Route::middleware('permission:artwork.approve')->group(function () {
            Route::post('requests/{artworkRequest}/approve', [ArtworkRequestController::class, 'approve'])->name('approve');
        });
    });
