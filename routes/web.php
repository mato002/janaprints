<?php

use App\Http\Controllers\BrandingAssetController;
use App\Http\Controllers\CustomerPaymentReceiptPublicController;
use App\Http\Controllers\MySessionsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EmployeeActivationController;
use App\Http\Controllers\RfqVendorPortalController;
use App\Http\Controllers\Storefront\PageController;
use App\Http\Controllers\Storefront\PublicContactMessageController;
use App\Http\Controllers\Storefront\PublicQuoteRequestController;
use App\Http\Controllers\Storefront\RobotsController;
use App\Http\Controllers\Storefront\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/branding/{path}', [BrandingAssetController::class, 'show'])
    ->where('path', '.*')
    ->name('branding.asset');

Route::get('/robots.txt', RobotsController::class)->name('storefront.robots');
Route::get('/sitemap.xml', SitemapController::class)->name('storefront.sitemap');

Route::controller(PageController::class)->group(function () {
    Route::get('/', 'home')->name('home');
    Route::get('/about', 'about')->name('storefront.about');
    Route::get('/services', 'services')->name('storefront.services');
    Route::get('/services/{slug}', 'serviceShow')->name('storefront.services.show');
    Route::get('/products', 'products')->name('storefront.products');
    Route::get('/products/{slug}', 'productShow')->name('storefront.products.show');
    Route::get('/portfolio', 'portfolio')->name('storefront.portfolio');
    Route::get('/gallery', 'gallery')->name('storefront.gallery');
    Route::get('/our-work', 'gallery')->name('storefront.our-work');
    Route::get('/blog', 'blog')->name('storefront.blog');
    Route::get('/contact', 'contact')->name('storefront.contact');
    Route::get('/request-quote', 'quote')->name('storefront.quote');
});

Route::post('/quote-request', [PublicQuoteRequestController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('public.quote-requests.store');

Route::post('/contact-message', [PublicContactMessageController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('public.contact-messages.store');

Route::get('/payment-receipt/{payment}', [CustomerPaymentReceiptPublicController::class, 'show'])
    ->middleware('signed')
    ->name('public.payment-receipt.show');

Route::prefix('rfq/respond')->name('rfq.portal.')->group(function () {
    Route::get('{token}', [RfqVendorPortalController::class, 'show'])->name('show');
    Route::post('{token}', [RfqVendorPortalController::class, 'submit'])->name('submit');
});

Route::prefix('activate')->name('employee.activate.')->group(function () {
    Route::get('{token}', [EmployeeActivationController::class, 'show'])->name('show');
    Route::post('{token}', [EmployeeActivationController::class, 'store'])->name('store');
});

Route::redirect('/dashboard', '/admin')->middleware(['auth', 'admin.auth', 'verified'])->name('dashboard');

require __DIR__.'/admin.php';

Route::middleware(['auth', 'tenant'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile/avatar', [ProfileController::class, 'destroyAvatar'])->name('profile.avatar.destroy');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/profile/sessions', [MySessionsController::class, 'index'])->name('profile.sessions.index');
    Route::post('/profile/sessions/logout-others', [MySessionsController::class, 'destroyOthers'])->name('profile.sessions.destroy-others');
    Route::delete('/profile/sessions/{userSession}', [MySessionsController::class, 'destroy'])->name('profile.sessions.destroy');
});

require __DIR__.'/auth.php';
