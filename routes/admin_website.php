<?php

use App\Http\Controllers\Admin\Website\WebsiteGalleryController;
use App\Http\Controllers\Admin\Website\WebsiteMediaController;
use App\Http\Controllers\Admin\Website\WebsiteSettingsController;
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
            Route::post('website/gallery/{websiteGalleryItem}/move', [WebsiteGalleryController::class, 'move'])
                ->name('website.gallery.move');
            Route::post('website/gallery/reorder', [WebsiteGalleryController::class, 'reorder'])
                ->name('website.gallery.reorder');
        });

        Route::middleware('permission:website.gallery.delete')->group(function () {
            Route::delete('website/gallery/{websiteGalleryItem}', [WebsiteGalleryController::class, 'destroy'])
                ->name('website.gallery.destroy');
        });

        Route::middleware('permission:website.media.view')->group(function () {
            Route::get('website/media', [WebsiteMediaController::class, 'index'])
                ->name('website.media.index');
            Route::get('website/media/{websiteMediaItem}/edit', [WebsiteMediaController::class, 'edit'])
                ->name('website.media.edit');
        });

        Route::middleware('permission:website.media.edit')->group(function () {
            Route::put('website/media/{websiteMediaItem}', [WebsiteMediaController::class, 'update'])
                ->name('website.media.update');
            Route::post('website/media/{websiteMediaItem}/toggle-active', [WebsiteMediaController::class, 'toggleActive'])
                ->name('website.media.toggle-active');
            Route::post('website/media/{websiteMediaItem}/reset-image', [WebsiteMediaController::class, 'resetImage'])
                ->name('website.media.reset-image');
            Route::delete('website/media/{websiteMediaItem}/image', [WebsiteMediaController::class, 'removeImage'])
                ->name('website.media.remove-image');
        });

        Route::middleware('permission:website.settings.view')->group(function () {
            Route::get('website/settings/footer-contact', [WebsiteSettingsController::class, 'footerContact'])
                ->name('website.settings.footer-contact');
            Route::get('website/settings/seo-global', [WebsiteSettingsController::class, 'seoGlobal'])
                ->name('website.settings.seo-global');
        });

        Route::middleware('permission:website.settings.edit')->group(function () {
            Route::put('website/settings/footer-contact', [WebsiteSettingsController::class, 'updateFooterContact'])
                ->name('website.settings.footer-contact.update');
            Route::put('website/settings/seo-global', [WebsiteSettingsController::class, 'updateSeoGlobal'])
                ->name('website.settings.seo-global.update');
            Route::post('website/settings/reset/{key}', [WebsiteSettingsController::class, 'resetSetting'])
                ->where('key', '[A-Za-z0-9\.\-_]+')
                ->name('website.settings.reset');
        });
    });
