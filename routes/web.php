<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\RfqVendorPortalController;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('rfq/respond')->name('rfq.portal.')->group(function () {
    Route::get('{token}', [RfqVendorPortalController::class, 'show'])->name('show');
    Route::post('{token}', [RfqVendorPortalController::class, 'submit'])->name('submit');
});

Route::redirect('/dashboard', '/admin')->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/admin.php';

Route::middleware(['auth', 'tenant'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile/avatar', [ProfileController::class, 'destroyAvatar'])->name('profile.avatar.destroy');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
