<?php

use App\Http\Controllers\Admin\AccessControlController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\TenantContextController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\Accounting\AccountingWorkspaceController;
use App\Http\Controllers\Admin\Administration\AdministrationWorkspaceController;
use App\Http\Controllers\Admin\Commercial\CommercialWorkspaceController;
use App\Http\Controllers\Admin\SupplyChain\SupplyChainWorkspaceController;
use App\Http\Controllers\Admin\WorkspaceController;
use Illuminate\Support\Facades\Route;
use App\Support\Navigation\WorkspacePresenter;

Route::middleware(['auth', 'verified', 'tenant', \App\Http\Middleware\CaptureWorkspaceNavigationQuery::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::get('workspaces/accounting', [AccountingWorkspaceController::class, 'hub'])
            ->name('workspaces.accounting');
        Route::get('workspaces/accounting/{section}', [AccountingWorkspaceController::class, 'section'])
            ->where('section', 'general-ledger|receivables|payables|tax|setup')
            ->name('workspaces.accounting.section');

        Route::get('workspaces/supply-chain', [SupplyChainWorkspaceController::class, 'hub'])
            ->name('workspaces.supply-chain');
        Route::get('workspaces/supply-chain/{section}', [SupplyChainWorkspaceController::class, 'section'])
            ->where('section', 'catalogue|store-operations|procurement|inventory-control|costing|assets|reports')
            ->name('workspaces.supply-chain.section');

        Route::get('workspaces/commercial', [CommercialWorkspaceController::class, 'hub'])
            ->name('workspaces.commercial');
        Route::get('workspaces/commercial/{section}', [CommercialWorkspaceController::class, 'section'])
            ->where('section', 'crm|sales|customer-service|point-of-sale|reports')
            ->name('workspaces.commercial.section');

        Route::get('workspaces/administration', [AdministrationWorkspaceController::class, 'hub'])
            ->name('workspaces.administration');
        Route::get('workspaces/administration/{section}', [AdministrationWorkspaceController::class, 'section'])
            ->where('section', 'security-access|organization|configuration|workflow-governance|integrations|system-operations')
            ->name('workspaces.administration.section');

        foreach (array_keys(config('workspaces', [])) as $workspace) {
            if (in_array($workspace, ['accounting', 'supply-chain', 'commercial', 'administration'], true)) {
                continue;
            }

            Route::get("workspaces/{$workspace}", fn (WorkspacePresenter $presenter) => app(WorkspaceController::class)->show(request(), $workspace, $presenter))
                ->name("workspaces.{$workspace}");
        }

        Route::post('context', [TenantContextController::class, 'update'])->name('context.update');

        Route::middleware('permission:users.view|roles.view')->group(function () {
            Route::get('access-control', [AccessControlController::class, 'index'])->name('access-control.index');
        });

        Route::middleware('permission:users.view')->group(function () {
            Route::get('users', [UserController::class, 'index'])->name('users.index');
        });

        Route::middleware('permission:users.create')->group(function () {
            Route::get('users/create', [UserController::class, 'create'])->name('users.create');
            Route::post('users', [UserController::class, 'store'])->name('users.store');
        });

        Route::middleware('permission:users.edit')->group(function () {
            Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
            Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
            Route::patch('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
            Route::patch('users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
        });

        Route::middleware('permission:users.delete')->group(function () {
            Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        });

        Route::middleware('permission:roles.view')->group(function () {
            Route::get('access-control/roles', [RoleController::class, 'index'])->name('access-control.roles');
            Route::get('roles', fn () => redirect()->route('admin.access-control.roles'))->name('roles.index');
            Route::get('access-control/matrix', [PermissionController::class, 'index'])->name('access-control.matrix');
            Route::get('permissions', fn () => redirect()->route('admin.access-control.matrix'))->name('permissions.index');
        });

        Route::middleware('permission:roles.create')->group(function () {
            Route::get('roles/create', [RoleController::class, 'create'])->name('roles.create');
            Route::post('roles', [RoleController::class, 'store'])->name('roles.store');
            Route::post('roles/{role}/duplicate', [RoleController::class, 'duplicate'])->name('roles.duplicate');
        });

        Route::middleware('permission:roles.view')->group(function () {
            Route::get('roles/{role}', [RoleController::class, 'show'])->name('roles.show');
        });

        Route::middleware('permission:roles.edit')->group(function () {
            Route::get('roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
            Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update');
            Route::get('roles/{role}/permissions', [PermissionController::class, 'edit'])->name('roles.permissions.edit');
            Route::put('roles/{role}/permissions', [PermissionController::class, 'update'])->name('roles.permissions.update');
        });

        Route::middleware('permission:roles.delete')->group(function () {
            Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
            Route::patch('roles/{role}/deactivate', [RoleController::class, 'deactivate'])->name('roles.deactivate');
            Route::patch('roles/{role}/reactivate', [RoleController::class, 'reactivate'])->name('roles.reactivate');
        });

        Route::middleware('permission:activity_logs.view')->group(function () {
            Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
        });

        Route::middleware('permission:companies.manage')->group(function () {
            Route::resource('companies', CompanyController::class)->except(['show']);
        });

        Route::middleware('permission:branches.manage')->group(function () {
            Route::resource('branches', BranchController::class)->except(['show']);
        });

        Route::middleware('permission:departments.manage')->group(function () {
            Route::resource('departments', DepartmentController::class)->except(['show']);
        });

        Route::middleware('permission:employees.manage')->group(function () {
            Route::resource('employees', EmployeeController::class)->except(['show']);
        });
    });

require __DIR__.'/admin_commercial.php';
require __DIR__.'/admin_crm.php';
require __DIR__.'/admin_quotations.php';
require __DIR__.'/admin_artwork.php';
require __DIR__.'/admin_sales_orders.php';
require __DIR__.'/admin_invoices.php';
require __DIR__.'/admin_payments.php';
require __DIR__.'/admin_payables.php';
require __DIR__.'/admin_production.php';
require __DIR__.'/admin_dispatch.php';
require __DIR__.'/admin_inventory.php';
require __DIR__.'/admin_procurement.php';
require __DIR__.'/admin_assets.php';
require __DIR__.'/admin_accounting.php';
require __DIR__.'/admin_tax.php';
require __DIR__.'/admin_settings.php';
require __DIR__.'/admin_communications.php';
require __DIR__.'/admin_communications_sms.php';
require __DIR__.'/admin_communication_logs.php';
require __DIR__.'/admin_communications_whatsapp.php';
require __DIR__.'/admin_communications_email.php';
require __DIR__.'/admin_communications_inbox.php';
require __DIR__.'/admin_reports.php';
require __DIR__.'/admin_integrations.php';
