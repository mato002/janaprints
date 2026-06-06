<?php

use App\Http\Controllers\Admin\Website\WebsiteGalleryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::middleware('permission:website.gallery.view')->group(function () {
            Route::get('website/gallery', [WebsiteGalleryController::class, 'index'])
                ->name('website.gallery.index');
            Route::get('website/gallery/{websiteGalleryItem}/edit', [WebsiteGalleryController::class, 'edit'])
                ->name('website.gallery.edit');
        });

        Route::middleware('permission:website.gallery.create')->group(function () {
            Route::get('website/gallery/create', [WebsiteGalleryController::class, 'create'])
                ->name('website.gallery.create');
            Route::post('website/gallery', [WebsiteGalleryController::class, 'store'])
                ->name('website.gallery.store');
        });

        Route::middleware('permission:website.gallery.edit')->group(function () {
            Route::put('website/gallery/{websiteGalleryItem}', [WebsiteGalleryController::class, 'update'])
                ->name('website.gallery.update');
            Route::post('website/gallery/reorder', [WebsiteGalleryController::class, 'reorder'])
                ->name('website.gallery.reorder');
        });

        Route::middleware('permission:website.gallery.delete')->group(function () {
            Route::delete('website/gallery/{websiteGalleryItem}', [WebsiteGalleryController::class, 'destroy'])
                ->name('website.gallery.destroy');
        });
    });
