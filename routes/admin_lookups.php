<?php

use App\Http\Controllers\Admin\LookupController;
use App\Http\Controllers\Admin\QuickCreateLookupController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin.auth', 'verified', 'tenant', \App\Http\Middleware\CaptureWorkspaceNavigationQuery::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::prefix('lookups')->name('lookups.')->group(function () {
            Route::get('companies', [LookupController::class, 'companies'])->name('companies');
            Route::get('branches', [LookupController::class, 'branches'])->name('branches');
            Route::get('customers', [LookupController::class, 'customers'])->name('customers');
            Route::get('vendors', [LookupController::class, 'vendors'])->name('vendors');
            Route::get('categories', [LookupController::class, 'categories'])->name('categories');
            Route::get('subcategories', [LookupController::class, 'subcategories'])->name('subcategories');
            Route::get('brands', [LookupController::class, 'brands'])->name('brands');
            Route::get('uoms', [LookupController::class, 'uoms'])->name('uoms');
            Route::get('items', [LookupController::class, 'items'])->name('items');
            Route::get('warehouses', [LookupController::class, 'warehouses'])->name('warehouses');
            Route::get('segments', [LookupController::class, 'segments'])->name('segments');
            Route::get('departments', [LookupController::class, 'departments'])->name('departments');
            Route::get('payroll_groups', [LookupController::class, 'payrollGroups'])->name('payroll_groups');
            Route::get('employees', [LookupController::class, 'employees'])->name('employees');
            Route::get('operators', [LookupController::class, 'operators'])->name('operators');
            Route::get('price_books', [LookupController::class, 'priceBooks'])->name('price_books');
            Route::get('leads', [LookupController::class, 'leads'])->name('leads');
            Route::get('lead_sources', [LookupController::class, 'leadSources'])->name('lead_sources');
            Route::get('artwork_types', [LookupController::class, 'artworkTypes'])->name('artwork_types');
            Route::get('quotations', [LookupController::class, 'quotations'])->name('quotations');
            Route::get('form_statuses', [LookupController::class, 'formStatuses'])->name('form_statuses');
            Route::get('customer_artworks', [LookupController::class, 'customerArtworks'])->name('customer_artworks');
            Route::get('customer_print_specifications', [LookupController::class, 'customerPrintSpecifications'])->name('customer_print_specifications');
            Route::get('sales_order_quotations', [LookupController::class, 'salesOrderQuotations'])->name('sales_order_quotations');
            Route::get('job_card_sales_orders', [LookupController::class, 'jobCardSalesOrders'])->name('job_card_sales_orders');
        });

        Route::middleware('permission:settings.manage')->group(function () {
            Route::get('form-statuses/quick-create', [QuickCreateLookupController::class, 'createFormStatus'])->name('form-statuses.quick-create');
            Route::post('form-statuses/quick-create', [QuickCreateLookupController::class, 'storeFormStatus'])->name('form-statuses.quick-store');
        });

        Route::middleware('permission:companies.manage')->group(function () {
            Route::get('companies/quick-create', [QuickCreateLookupController::class, 'createCompany'])->name('companies.quick-create');
            Route::post('companies/quick-create', [QuickCreateLookupController::class, 'storeCompany'])->name('companies.quick-store');
        });

        Route::middleware('permission:branches.manage')->group(function () {
            Route::get('branches/quick-create', [QuickCreateLookupController::class, 'createBranch'])->name('branches.quick-create');
            Route::post('branches/quick-create', [QuickCreateLookupController::class, 'storeBranch'])->name('branches.quick-store');
        });

        Route::middleware('permission:departments.manage')->group(function () {
            Route::get('departments/quick-create', [QuickCreateLookupController::class, 'createDepartment'])->name('departments.quick-create');
            Route::post('departments/quick-create', [QuickCreateLookupController::class, 'storeDepartment'])->name('departments.quick-store');
        });

        Route::middleware('permission:hr.compensation.create')->group(function () {
            Route::get('payroll-groups/quick-create', [QuickCreateLookupController::class, 'createPayrollGroup'])->name('payroll-groups.quick-create');
            Route::post('payroll-groups/quick-create', [QuickCreateLookupController::class, 'storePayrollGroup'])->name('payroll-groups.quick-store');
        });

        Route::middleware('permission:employees.manage')->group(function () {
            Route::get('employees/quick-create', [QuickCreateLookupController::class, 'createEmployee'])->name('employees.quick-create');
            Route::post('employees/quick-create', [QuickCreateLookupController::class, 'storeEmployee'])->name('employees.quick-store');
            Route::get('operators/quick-create', [QuickCreateLookupController::class, 'createOperator'])->name('operators.quick-create');
            Route::post('operators/quick-create', [QuickCreateLookupController::class, 'storeOperator'])->name('operators.quick-store');
        });

        Route::middleware('permission:quotations.create')->group(function () {
            Route::get('quotations/quick-create', [QuickCreateLookupController::class, 'createQuotation'])->name('quotations.quick-create');
            Route::post('quotations/quick-create', [QuickCreateLookupController::class, 'storeQuotation'])->name('quotations.quick-store');
        });

        Route::prefix('procurement')->name('procurement.')->group(function () {
            Route::middleware('permission:procurement.vendors.create')->group(function () {
                Route::get('vendors/quick-create', [QuickCreateLookupController::class, 'createVendor'])->name('vendors.quick-create');
                Route::post('vendors/quick-create', [QuickCreateLookupController::class, 'storeVendor'])->name('vendors.quick-store');
            });
        });

        Route::prefix('inventory')->name('inventory.')->group(function () {
            Route::middleware('permission:catalogue.create')->group(function () {
                Route::get('catalogue/categories/quick-create', [QuickCreateLookupController::class, 'createCategory'])->name('catalogue.categories.quick-create');
                Route::post('catalogue/categories/quick-create', [QuickCreateLookupController::class, 'storeCategory'])->name('catalogue.categories.quick-store');
                Route::get('catalogue/subcategories/quick-create', [QuickCreateLookupController::class, 'createSubcategory'])->name('catalogue.subcategories.quick-create');
                Route::post('catalogue/subcategories/quick-create', [QuickCreateLookupController::class, 'storeSubcategory'])->name('catalogue.subcategories.quick-store');
                Route::get('catalogue/brands/quick-create', [QuickCreateLookupController::class, 'createBrand'])->name('catalogue.brands.quick-create');
                Route::post('catalogue/brands/quick-create', [QuickCreateLookupController::class, 'storeBrand'])->name('catalogue.brands.quick-store');
                Route::get('catalogue/uoms/quick-create', [QuickCreateLookupController::class, 'createUom'])->name('catalogue.uoms.quick-create');
                Route::post('catalogue/uoms/quick-create', [QuickCreateLookupController::class, 'storeUom'])->name('catalogue.uoms.quick-store');
                Route::get('items/quick-create', [QuickCreateLookupController::class, 'createItem'])->name('items.quick-create');
                Route::post('items/quick-create', [QuickCreateLookupController::class, 'storeItem'])->name('items.quick-store');
            });

            Route::middleware('permission:inventory.create')->group(function () {
                Route::get('warehouses/quick-create', [QuickCreateLookupController::class, 'createWarehouse'])->name('warehouses.quick-create');
                Route::post('warehouses/quick-create', [QuickCreateLookupController::class, 'storeWarehouse'])->name('warehouses.quick-store');
            });
        });
    });
