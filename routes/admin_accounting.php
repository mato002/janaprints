<?php

use App\Http\Controllers\Admin\Accounting\AccountingDashboardController;
use App\Http\Controllers\Admin\Accounting\AccountingPeriodController;
use App\Http\Controllers\Admin\Accounting\BankAccountController;
use App\Http\Controllers\Admin\Accounting\BankReconciliationController;
use App\Http\Controllers\Admin\Accounting\BudgetController;
use App\Http\Controllers\Admin\Accounting\CurrencyController;
use App\Http\Controllers\Admin\Accounting\ExchangeRateController;
use App\Http\Controllers\Admin\Accounting\FiscalYearController;
use App\Http\Controllers\Admin\Accounting\FinancialReportController;
use App\Http\Controllers\Admin\Accounting\GeneralLedgerController;
use App\Http\Controllers\Admin\Accounting\ChartOfAccountsExplorerController;
use App\Http\Controllers\Admin\Accounting\GlAccountController;
use App\Http\Controllers\Admin\Accounting\JournalController;
use App\Http\Controllers\Admin\Accounting\PostingRuleController;
use App\Http\Controllers\Admin\Accounting\PostingTemplateController;
use App\Http\Controllers\Admin\Accounting\TrialBalanceController;
use App\Http\Controllers\Admin\Export\AccountingListingExportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant'])
    ->prefix('admin/accounting')
    ->name('admin.accounting.')
    ->group(function () {
        Route::get('exports/{listing}/{format}', [AccountingListingExportController::class, 'download'])
            ->where('format', 'csv|excel|pdf')
            ->name('exports');

        Route::middleware('permission:accounting.dashboard.view')->group(function () {
            Route::get('dashboard', AccountingDashboardController::class)->name('dashboard');
        });

        Route::middleware('permission:accounting.chart.view')->group(function () {
            Route::get('accounts', [GlAccountController::class, 'index'])->name('accounts.index');
            Route::get('accounts/explorer/groups', [ChartOfAccountsExplorerController::class, 'groups'])->name('accounts.explorer.groups');
            Route::get('accounts/explorer/accounts', [ChartOfAccountsExplorerController::class, 'accounts'])->name('accounts.explorer.accounts');
            Route::get('accounts/explorer/search', [ChartOfAccountsExplorerController::class, 'search'])->name('accounts.explorer.search');
            Route::get('accounts/{account}/explorer-panel', [ChartOfAccountsExplorerController::class, 'panel'])->name('accounts.explorer.panel');
        });

        Route::middleware('permission:accounting.chart.edit')->group(function () {
            Route::post('accounts/{account}/deactivate', [ChartOfAccountsExplorerController::class, 'deactivate'])->name('accounts.deactivate');
        });

        Route::middleware('permission:accounting.chart.create')->group(function () {
            Route::get('accounts/create', [GlAccountController::class, 'create'])->name('accounts.create');
            Route::post('accounts', [GlAccountController::class, 'store'])->name('accounts.store');
        });

        Route::middleware('permission:accounting.chart.view')->group(function () {
            Route::get('accounts/{account}', [GlAccountController::class, 'show'])->name('accounts.show');
        });

        Route::middleware('permission:accounting.chart.edit')->group(function () {
            Route::get('accounts/{account}/edit', [GlAccountController::class, 'edit'])->name('accounts.edit');
            Route::put('accounts/{account}', [GlAccountController::class, 'update'])->name('accounts.update');
        });

        Route::middleware('permission:accounting.chart.delete')->group(function () {
            Route::delete('accounts/{account}', [GlAccountController::class, 'destroy'])->name('accounts.destroy');
        });

        Route::middleware('permission:accounting.chart.lock')->group(function () {
            Route::post('accounts/{account}/lock', [GlAccountController::class, 'lock'])->name('accounts.lock');
            Route::post('accounts/{account}/unlock', [GlAccountController::class, 'unlock'])->name('accounts.unlock');
        });

        Route::middleware('permission:accounting.periods.view')->group(function () {
            Route::get('periods', [FiscalYearController::class, 'index'])->name('periods.index');
            Route::get('periods/fiscal-years/{fiscalYear}', [FiscalYearController::class, 'show'])->name('periods.fiscal-years.show');
        });

        Route::middleware('permission:accounting.periods.create')->group(function () {
            Route::get('periods/create', [FiscalYearController::class, 'create'])->name('periods.create');
            Route::post('periods', [FiscalYearController::class, 'store'])->name('periods.store');
        });

        Route::middleware('permission:accounting.periods.close')->group(function () {
            Route::post('periods/fiscal-years/{fiscalYear}/year-end-prep', [FiscalYearController::class, 'yearEndPrep'])->name('periods.fiscal-years.year-end-prep');
            Route::post('periods/fiscal-years/{fiscalYear}/close', [FiscalYearController::class, 'close'])->name('periods.fiscal-years.close');
            Route::post('periods/{period}/close', [AccountingPeriodController::class, 'close'])->name('periods.close');
        });

        Route::middleware('permission:accounting.periods.lock')->group(function () {
            Route::post('periods/fiscal-years/{fiscalYear}/lock', [FiscalYearController::class, 'lock'])->name('periods.fiscal-years.lock');
            Route::post('periods/{period}/lock', [AccountingPeriodController::class, 'lock'])->name('periods.lock');
        });

        Route::middleware('permission:accounting.periods.reopen')->group(function () {
            Route::post('periods/fiscal-years/{fiscalYear}/reopen', [FiscalYearController::class, 'reopen'])->name('periods.fiscal-years.reopen');
            Route::post('periods/{period}/reopen', [AccountingPeriodController::class, 'reopen'])->name('periods.reopen');
            Route::post('periods/{period}/unlock', [AccountingPeriodController::class, 'unlock'])->name('periods.unlock');
        });

        Route::middleware('permission:accounting.periods.manage')->group(function () {
            Route::post('periods/{period}/set-current', [AccountingPeriodController::class, 'setCurrent'])->name('periods.set-current');
        });

        Route::middleware('permission:accounting.journals.view')->group(function () {
            Route::get('journals', [JournalController::class, 'index'])->name('journals.index');
            Route::get('ledger', [GeneralLedgerController::class, 'index'])->name('ledger.index');
            Route::get('trial-balance', [TrialBalanceController::class, 'index'])->name('trial-balance.index');
        });

        Route::middleware('permission:accounting.journals.create')->group(function () {
            Route::get('journals/create', [JournalController::class, 'create'])->name('journals.create');
            Route::post('journals', [JournalController::class, 'store'])->name('journals.store');
        });

        Route::middleware('permission:accounting.journals.view')->group(function () {
            Route::get('journals/{journal}', [JournalController::class, 'show'])->name('journals.show');
        });

        Route::middleware('permission:accounting.journals.create')->group(function () {
            Route::get('journals/{journal}/edit', [JournalController::class, 'edit'])->name('journals.edit');
            Route::put('journals/{journal}', [JournalController::class, 'update'])->name('journals.update');
            Route::delete('journals/{journal}', [JournalController::class, 'destroy'])->name('journals.destroy');
        });

        Route::middleware('permission:accounting.journals.post')->group(function () {
            Route::post('journals/{journal}/post', [JournalController::class, 'post'])->name('journals.post');
        });

        Route::middleware('permission:accounting.journals.reverse')->group(function () {
            Route::post('journals/{journal}/reverse', [JournalController::class, 'reverse'])->name('journals.reverse');
        });

        Route::middleware('permission:accounting.posting_rules.view|accounting.posting_rules.audit|accounting.posting.view')->group(function () {
            Route::get('posting/rules', [PostingRuleController::class, 'index'])->name('posting.rules.index');
            Route::get('posting/rules/{rule}', [PostingRuleController::class, 'show'])->name('posting.rules.show');
        });

        Route::middleware('permission:accounting.posting_rules.manage|accounting.posting.manage')->group(function () {
            Route::get('posting/rules-create', [PostingRuleController::class, 'create'])->name('posting.rules.create');
            Route::post('posting/rules', [PostingRuleController::class, 'store'])->name('posting.rules.store');
            Route::get('posting/rules/{rule}/edit', [PostingRuleController::class, 'edit'])->name('posting.rules.edit');
            Route::put('posting/rules/{rule}', [PostingRuleController::class, 'update'])->name('posting.rules.update');
            Route::post('posting/rules/{rule}/toggle', [PostingRuleController::class, 'toggle'])->name('posting.rules.toggle');
        });

        Route::middleware('permission:accounting.posting.view')->group(function () {
            Route::get('posting/templates', [PostingTemplateController::class, 'index'])->name('posting.templates.index');
            Route::get('posting/templates/{template}', [PostingTemplateController::class, 'show'])->name('posting.templates.show');
        });

        Route::middleware('permission:accounting.posting.manage')->group(function () {
            Route::get('posting/templates-create', [PostingTemplateController::class, 'create'])->name('posting.templates.create');
            Route::post('posting/templates', [PostingTemplateController::class, 'store'])->name('posting.templates.store');
            Route::get('posting/templates/{template}/edit', [PostingTemplateController::class, 'edit'])->name('posting.templates.edit');
            Route::put('posting/templates/{template}', [PostingTemplateController::class, 'update'])->name('posting.templates.update');
            Route::post('posting/templates/{template}/toggle', [PostingTemplateController::class, 'toggle'])->name('posting.templates.toggle');
        });

        Route::middleware('permission:accounting.reports.view')->prefix('reports')->name('reports.')->group(function () {
            Route::get('trial-balance', [FinancialReportController::class, 'trialBalance'])->name('trial-balance');
            Route::get('balance-sheet', [FinancialReportController::class, 'balanceSheet'])->name('balance-sheet');
            Route::get('profit-and-loss', [FinancialReportController::class, 'profitAndLoss'])->name('profit-and-loss');
            Route::get('cash-flow', [FinancialReportController::class, 'cashFlow'])->name('cash-flow');
            Route::get('general-ledger', [FinancialReportController::class, 'generalLedger'])->name('general-ledger');
            Route::get('financial-integrity', [FinancialReportController::class, 'financialIntegrity'])->name('financial-integrity');
        });

        Route::middleware('permission:accounting.bank.view')->prefix('bank')->name('bank.')->group(function () {
            Route::get('accounts', [BankAccountController::class, 'index'])->name('accounts.index');
            Route::get('reconciliation', [BankReconciliationController::class, 'index'])->name('reconciliation.index');
            Route::get('reconciliation/{statement}', [BankReconciliationController::class, 'show'])->name('reconciliation.show');
        });

        Route::middleware('permission:accounting.bank.manage')->prefix('bank')->name('bank.')->group(function () {
            Route::get('accounts/create', [BankAccountController::class, 'create'])->name('accounts.create');
            Route::post('accounts', [BankAccountController::class, 'store'])->name('accounts.store');
            Route::get('reconciliation-create', [BankReconciliationController::class, 'create'])->name('reconciliation.create');
            Route::post('reconciliation', [BankReconciliationController::class, 'store'])->name('reconciliation.store');
            Route::post('reconciliation/{statement}/match', [BankReconciliationController::class, 'match'])->name('reconciliation.match');
            Route::post('reconciliation/{statement}/import-lines', [BankReconciliationController::class, 'importLines'])->name('reconciliation.import-lines');
            Route::post('reconciliation/{statement}/reconcile', [BankReconciliationController::class, 'reconcile'])->name('reconciliation.reconcile');
            Route::post('statement-lines/{line}/unmatch', [BankReconciliationController::class, 'unmatch'])->name('statement-lines.unmatch');
        });

        Route::middleware('permission:accounting.currencies.view')->prefix('currencies')->name('currencies.')->group(function () {
            Route::get('/', [CurrencyController::class, 'index'])->name('index');
            Route::get('rates', [ExchangeRateController::class, 'index'])->name('rates.index');
        });

        Route::middleware('permission:accounting.currencies.manage')->prefix('currencies')->name('currencies.')->group(function () {
            Route::post('rates', [ExchangeRateController::class, 'store'])->name('rates.store');
        });

        Route::prefix('budgets')->name('budgets.')->group(function () {
            Route::middleware('permission:accounting.budgets.manage')->group(function () {
                Route::get('create', [BudgetController::class, 'create'])->name('create');
                Route::post('/', [BudgetController::class, 'store'])->name('store');
                Route::post('{budget}/activate', [BudgetController::class, 'activate'])->name('activate');
            });

            Route::middleware('permission:accounting.budgets.view')->group(function () {
                Route::get('/', [BudgetController::class, 'index'])->name('index');
                Route::get('{budget}/vs-actual', [BudgetController::class, 'vsActual'])->name('vs-actual');
                Route::get('{budget}', [BudgetController::class, 'show'])->name('show');
            });
        });
    });
