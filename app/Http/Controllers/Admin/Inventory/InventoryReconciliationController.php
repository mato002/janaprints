<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Enums\InventoryReconciliationStatus;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Inventory\InventoryReconciliation;
use App\Support\Inventory\InventoryReconciliationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InventoryReconciliationController extends Controller
{
    use ScopesToTenant;

    public function index(): View
    {
        $this->authorize('viewAny', InventoryReconciliation::class);

        $pending = $this->scopeToTenant(
            InventoryReconciliation::query()
                ->where('status', InventoryReconciliationStatus::Pending)
                ->with(['stockCount.warehouse', 'stockCount.items'])
                ->latest()
        )->paginate(config('platform.pagination.default', 15), ['*'], 'pending_page');

        $approved = $this->scopeToTenant(
            InventoryReconciliation::query()
                ->where('status', InventoryReconciliationStatus::Approved)
                ->with(['stockCount.warehouse'])
                ->latest()
        )->paginate(config('platform.pagination.default', 15), ['*'], 'approved_page');

        $posted = $this->scopeToTenant(
            InventoryReconciliation::query()
                ->whereIn('status', [InventoryReconciliationStatus::Posted, InventoryReconciliationStatus::Closed])
                ->with(['stockCount.warehouse', 'poster', 'stockAdjustment'])
                ->latest('posted_at')
        )->paginate(config('platform.pagination.default', 15), ['*'], 'posted_page');

        return view('admin.inventory.control.reconciliations.index', compact('pending', 'approved', 'posted'));
    }

    public function show(InventoryReconciliation $reconciliation): View
    {
        $this->authorize('view', $reconciliation);

        $reconciliation->load([
            'stockCount.warehouse',
            'stockCount.items.inventoryItem',
            'stockCount.approver',
            'stockCount.poster',
            'approver',
            'poster',
            'stockAdjustment',
        ]);

        $auditHistory = ActivityLog::query()
            ->where('model_type', InventoryReconciliation::class)
            ->where('model_id', $reconciliation->id)
            ->orWhere(function ($q) use ($reconciliation) {
                $q->where('model_type', \App\Models\Inventory\StockCount::class)
                    ->where('model_id', $reconciliation->stock_count_id);
            })
            ->latest('created_at')
            ->limit(50)
            ->get();

        return view('admin.inventory.control.reconciliations.show', compact('reconciliation', 'auditHistory'));
    }

    public function approve(InventoryReconciliation $reconciliation): RedirectResponse
    {
        $this->authorize('approve', $reconciliation);

        try {
            InventoryReconciliationService::approve($reconciliation, (int) auth()->id());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('status', __('Reconciliation approved.'));
    }

    public function post(InventoryReconciliation $reconciliation): RedirectResponse
    {
        $this->authorize('post', $reconciliation);

        try {
            InventoryReconciliationService::post($reconciliation, (int) auth()->id());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('status', __('Reconciliation posted to inventory ledger.'));
    }
}
