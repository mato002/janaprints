<?php

use App\Http\Controllers\Admin\ApprovalDelegationsController;
use App\Http\Controllers\Admin\Governance\EscalationsController;
use App\Http\Controllers\Admin\Governance\WorkflowRulesController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::middleware('permission:governance.delegations.view')->group(function () {
            Route::get('governance/delegations', [ApprovalDelegationsController::class, 'index'])->name('governance.delegations.index');
        });

        Route::middleware('permission:governance.delegations.create')->group(function () {
            Route::get('governance/delegations/create', [ApprovalDelegationsController::class, 'create'])->name('governance.delegations.create');
            Route::post('governance/delegations', [ApprovalDelegationsController::class, 'store'])->name('governance.delegations.store');
        });

        Route::middleware('permission:governance.delegations.view')->group(function () {
            Route::get('governance/delegations/{approvalDelegation}/edit', [ApprovalDelegationsController::class, 'edit'])->name('governance.delegations.edit');
        });

        Route::middleware('permission:governance.delegations.manage')->group(function () {
            Route::put('governance/delegations/{approvalDelegation}', [ApprovalDelegationsController::class, 'update'])->name('governance.delegations.update');
            Route::patch('governance/delegations/{approvalDelegation}/cancel', [ApprovalDelegationsController::class, 'cancel'])->name('governance.delegations.cancel');
        });

        Route::middleware('permission:governance.escalations.view')->group(function () {
            Route::get('governance/escalations', [EscalationsController::class, 'index'])->name('governance.escalations.index');
        });

        Route::middleware('permission:governance.escalations.manage')->group(function () {
            Route::get('governance/escalations/create', [EscalationsController::class, 'create'])->name('governance.escalations.create');
            Route::post('governance/escalations', [EscalationsController::class, 'store'])->name('governance.escalations.store');
            Route::get('governance/escalations/{escalation}/edit', [EscalationsController::class, 'edit'])->name('governance.escalations.edit');
            Route::put('governance/escalations/{escalation}', [EscalationsController::class, 'update'])->name('governance.escalations.update');
            Route::patch('governance/escalations/{escalation}/activate', [EscalationsController::class, 'activate'])->name('governance.escalations.activate');
            Route::patch('governance/escalations/{escalation}/deactivate', [EscalationsController::class, 'deactivate'])->name('governance.escalations.deactivate');
        });

        Route::prefix('governance/workflow-rules')->name('governance.workflow-rules.')->group(function () {
            Route::middleware('permission:governance.workflow.create')->group(function () {
                Route::get('create', [WorkflowRulesController::class, 'create'])->name('create');
                Route::post('/', [WorkflowRulesController::class, 'store'])->name('store');
            });

            Route::middleware('permission:governance.workflow.manage')->group(function () {
                Route::get('{workflowRule}/edit', [WorkflowRulesController::class, 'edit'])->name('edit');
                Route::put('{workflowRule}', [WorkflowRulesController::class, 'update'])->name('update');
                Route::patch('{workflowRule}/activate', [WorkflowRulesController::class, 'activate'])->name('activate');
                Route::patch('{workflowRule}/deactivate', [WorkflowRulesController::class, 'deactivate'])->name('deactivate');
            });

            Route::middleware('permission:governance.workflow.view')->group(function () {
                Route::get('/', [WorkflowRulesController::class, 'index'])->name('index');
            });
        });
    });
