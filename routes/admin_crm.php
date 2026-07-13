<?php

use App\Http\Controllers\Admin\Crm\CrmDashboardController;
use App\Http\Controllers\Admin\Crm\CustomerActivityController;
use App\Http\Controllers\Admin\Crm\CustomerArtworkController;
use App\Http\Controllers\Admin\Crm\CustomerPrintSpecificationController;
use App\Http\Controllers\Admin\Crm\CustomerProductSerialProfileController;
use App\Http\Controllers\Admin\Crm\CustomerContactController;
use App\Http\Controllers\Admin\Crm\CustomerController;
use App\Http\Controllers\Admin\Crm\CustomerFileController;
use App\Http\Controllers\Admin\Crm\CustomerNoteController;
use App\Http\Controllers\Admin\Crm\CustomerSegmentController;
use App\Http\Controllers\Admin\Crm\LeadController;
use App\Http\Controllers\Admin\Crm\LeadFollowUpController;
use App\Http\Controllers\Admin\QuickCreateLookupController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant'])
    ->prefix('admin/crm')
    ->name('admin.crm.')
    ->group(function () {
        Route::get('/', CrmDashboardController::class)
            ->middleware('permission:crm.customers.view')
            ->name('dashboard');

        Route::middleware('permission:crm.customers.view')->group(function () {
            Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
            Route::get('customers/export/{format}', [CustomerController::class, 'export'])
                ->where('format', 'csv|excel|pdf')
                ->name('customers.export');
            Route::get('segments', [CustomerSegmentController::class, 'index'])->name('segments.index');
        });

        Route::middleware('permission:crm.customers.create')->group(function () {
            Route::get('customers/create', [CustomerController::class, 'create'])->name('customers.create');
            Route::get('customers/quick-create', [QuickCreateLookupController::class, 'createCustomer'])->name('customers.quick-create');
            Route::post('customers/quick-create', [QuickCreateLookupController::class, 'storeCustomer'])->name('customers.quick-store');
            Route::post('customers', [CustomerController::class, 'store'])->name('customers.store');
            Route::get('segments/create', [CustomerSegmentController::class, 'create'])->name('segments.create');
            Route::get('segments/quick-create', [QuickCreateLookupController::class, 'createSegment'])->name('segments.quick-create');
            Route::post('segments/quick-create', [QuickCreateLookupController::class, 'storeSegment'])->name('segments.quick-store');
            Route::post('segments', [CustomerSegmentController::class, 'store'])->name('segments.store');
        });

        Route::middleware('permission:crm.customers.view')->group(function () {
            Route::get('customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
            Route::get('customers/{customer}/artworks/{customerArtwork}/preview', [CustomerArtworkController::class, 'preview'])->name('customers.artworks.preview');
        });

        Route::middleware('permission:sales_orders.create')->group(function () {
            Route::post('customers/{customer}/repeat-order/{salesOrder}', [\App\Http\Controllers\Admin\Sales\DirectCustomerOrderController::class, 'repeat'])->name('customers.repeat-order');
        });

        Route::middleware('permission:crm.customers.edit')->group(function () {
            Route::get('customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
            Route::put('customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
            Route::post('customers/{customer}/deactivate', [CustomerController::class, 'deactivate'])->name('customers.deactivate');
            Route::post('customers/{customer}/portal-invite', [CustomerController::class, 'inviteToPortal'])->name('customers.portal-invite');
            Route::post('customers/{customer}/contacts', [CustomerContactController::class, 'store'])->name('customers.contacts.store');
            Route::delete('customers/{customer}/contacts/{contact}', [CustomerContactController::class, 'destroy'])->name('customers.contacts.destroy');
            Route::post('customers/{customer}/notes', [CustomerNoteController::class, 'store'])->name('customers.notes.store');
            Route::delete('customers/{customer}/notes/{note}', [CustomerNoteController::class, 'destroy'])->name('customers.notes.destroy');
            Route::post('customers/{customer}/files', [CustomerFileController::class, 'store'])->name('customers.files.store');
            Route::delete('customers/{customer}/files/{file}', [CustomerFileController::class, 'destroy'])->name('customers.files.destroy');
            Route::post('customers/{customer}/artworks', [CustomerArtworkController::class, 'store'])->name('customers.artworks.store');
            Route::get('customers/{customer}/print-specifications/{printSpecification}', [CustomerPrintSpecificationController::class, 'show'])->name('customers.print-specifications.show');
            Route::post('customers/{customer}/print-specifications/{printSpecification}/transition', [CustomerPrintSpecificationController::class, 'transition'])->name('customers.print-specifications.transition');
            Route::get('customers/{customer}/print-specifications/create', [CustomerPrintSpecificationController::class, 'create'])->name('customers.print-specifications.create');
            Route::post('customers/{customer}/print-specifications', [CustomerPrintSpecificationController::class, 'store'])->name('customers.print-specifications.store');
            Route::get('customers/{customer}/print-specifications/{printSpecification}/edit', [CustomerPrintSpecificationController::class, 'edit'])->name('customers.print-specifications.edit');
            Route::put('customers/{customer}/print-specifications/{printSpecification}', [CustomerPrintSpecificationController::class, 'update'])->name('customers.print-specifications.update');
            Route::post('customers/{customer}/print-specifications/{printSpecification}/artworks', [CustomerPrintSpecificationController::class, 'uploadArtwork'])->name('customers.print-specifications.artworks.store');
            Route::post('customers/{customer}/print-specifications/{printSpecification}/serial-profile', [CustomerPrintSpecificationController::class, 'saveSerialProfileFromSpec'])->name('customers.print-specifications.serial-profile.store');
            Route::post('customers/{customer}/serial-profiles', [CustomerProductSerialProfileController::class, 'store'])->name('customers.serial-profiles.store');
            Route::delete('customers/{customer}/serial-profiles/{profile}', [CustomerProductSerialProfileController::class, 'destroy'])->name('customers.serial-profiles.destroy');
            Route::get('segments/{segment}/edit', [CustomerSegmentController::class, 'edit'])->name('segments.edit');
            Route::put('segments/{segment}', [CustomerSegmentController::class, 'update'])->name('segments.update');
        });

        Route::middleware('permission:crm.customers.delete')->group(function () {
            Route::delete('customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
            Route::delete('segments/{segment}', [CustomerSegmentController::class, 'destroy'])->name('segments.destroy');
        });

        Route::middleware('permission:crm.leads.view')->group(function () {
            Route::get('leads', [LeadController::class, 'index'])->name('leads.index');
        });

        Route::middleware('permission:crm.leads.create')->group(function () {
            Route::get('leads/create', [LeadController::class, 'create'])->name('leads.create');
            Route::get('leads/quick-create', [QuickCreateLookupController::class, 'createLead'])->name('leads.quick-create');
            Route::post('leads/quick-create', [QuickCreateLookupController::class, 'storeLead'])->name('leads.quick-store');
            Route::get('lead-sources/quick-create', [QuickCreateLookupController::class, 'createLeadSource'])->name('lead-sources.quick-create');
            Route::post('lead-sources/quick-create', [QuickCreateLookupController::class, 'storeLeadSource'])->name('lead-sources.quick-store');
            Route::post('leads', [LeadController::class, 'store'])->name('leads.store');
        });

        Route::middleware('permission:crm.leads.view')->group(function () {
            Route::get('leads/{lead}', [LeadController::class, 'show'])->name('leads.show');
        });

        Route::middleware('permission:quotations.create')->group(function () {
            Route::get('leads/{lead}/quotation/create', [LeadController::class, 'createQuotation'])->name('leads.quotation.create');
            Route::post('leads/{lead}/quotation/quick', [LeadController::class, 'quickQuotation'])->name('leads.quotation.quick');
        });

        Route::middleware('permission:crm.leads.edit')->group(function () {
            Route::get('leads/{lead}/edit', [LeadController::class, 'edit'])->name('leads.edit');
            Route::put('leads/{lead}', [LeadController::class, 'update'])->name('leads.update');
            Route::post('leads/{lead}/convert', [LeadController::class, 'convert'])->name('leads.convert');
            Route::post('leads/{lead}/mark-lost', [LeadController::class, 'markLost'])->name('leads.mark-lost');
            Route::post('leads/{lead}/follow-ups', [LeadFollowUpController::class, 'store'])->name('leads.follow-ups.store');
            Route::patch('leads/{lead}/follow-ups/{followUp}', [LeadFollowUpController::class, 'update'])->name('leads.follow-ups.update');
            Route::delete('leads/{lead}/follow-ups/{followUp}', [LeadFollowUpController::class, 'destroy'])->name('leads.follow-ups.destroy');
        });

        Route::middleware('permission:crm.leads.delete')->group(function () {
            Route::delete('leads/{lead}', [LeadController::class, 'destroy'])->name('leads.destroy');
        });

        Route::middleware('permission:commercial.activities.create')->group(function () {
            Route::post('customers/{customer}/activities', [CustomerActivityController::class, 'storeForCustomer'])->name('customers.activities.store');
            Route::post('leads/{lead}/activities', [CustomerActivityController::class, 'storeForLead'])->name('leads.activities.store');
        });

        Route::middleware('permission:commercial.activities.delete')->group(function () {
            Route::delete('activities/{activity}', [CustomerActivityController::class, 'destroy'])->name('activities.destroy');
        });

        Route::redirect('activities', '/admin/commercial/activities')
            ->middleware('permission:commercial.activities.view')
            ->name('activities.index');
    });
