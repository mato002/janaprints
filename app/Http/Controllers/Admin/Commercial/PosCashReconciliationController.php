<?php

namespace App\Http\Controllers\Admin\Commercial;

use App\Enums\PosReconciliationStatus;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Admin\Crm\Concerns\ResolvesCrmTenant;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Pos\PosCashReconciliation;
use App\Models\User;
use App\Support\Commercial\PosCashReconciliationReadiness;
use App\Support\Commercial\PosCashReconciliationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PosCashReconciliationController extends Controller
{
    use ResolvesCrmTenant, ScopesToTenant;

    public function __construct(
        protected PosCashReconciliationService $reconciliations,
        protected PosCashReconciliationReadiness $readiness,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', PosCashReconciliation::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds($request);
        $user = $request->user();

        $scopeBranch = $user?->can('commercial.pos.sessions.admin') && $request->filled('branch_id')
            ? (int) $request->input('branch_id')
            : ($user?->can('commercial.pos.sessions.admin') ? null : $branchId);

        $query = PosCashReconciliation::query()
            ->with(['cashier:id,name', 'branch:id,name', 'session:id,session_number'])
            ->where('company_id', $companyId);

        if ($scopeBranch !== null) {
            $query->where('branch_id', $scopeBranch);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('variance_type')) {
            $query->where('variance_type', $request->string('variance_type'));
        }

        if ($request->filled('cashier_id')) {
            $query->where('cashier_id', (int) $request->input('cashier_id'));
        }

        $reconciliations = $query
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.commercial.pos.reconciliation.index', [
            'reconciliations' => $reconciliations,
            'stats' => $this->reconciliations->dashboardStats($companyId, $scopeBranch),
            'filters' => $request->only(['status', 'variance_type', 'branch_id', 'cashier_id']),
            'branches' => $user?->can('commercial.pos.sessions.admin')
                ? Branch::query()->where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get(['id', 'name'])
                : collect(),
            'cashiers' => User::query()->where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'readiness' => $this->readiness->assess(),
            'report_ready' => $this->readiness->isReady(),
        ]);
    }

    public function history(Request $request): View
    {
        $this->authorize('viewAny', PosCashReconciliation::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds($request);
        $user = $request->user();

        $scopeBranch = $user?->can('commercial.pos.sessions.admin') && $request->filled('branch_id')
            ? (int) $request->input('branch_id')
            : ($user?->can('commercial.pos.sessions.admin') ? null : $branchId);

        $query = PosCashReconciliation::query()
            ->with(['cashier:id,name', 'branch:id,name', 'session:id,session_number'])
            ->where('company_id', $companyId)
            ->whereIn('status', [PosReconciliationStatus::Approved, PosReconciliationStatus::Rejected]);

        if ($scopeBranch !== null) {
            $query->where('branch_id', $scopeBranch);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $reconciliations = $query
            ->orderByDesc('approved_at')
            ->orderByDesc('rejected_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.commercial.pos.reconciliation.history', [
            'reconciliations' => $reconciliations,
            'filters' => $request->only(['status', 'branch_id']),
            'branches' => $user?->can('commercial.pos.sessions.admin')
                ? Branch::query()->where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get(['id', 'name'])
                : collect(),
        ]);
    }

    public function show(Request $request, PosCashReconciliation $reconciliation): View
    {
        $this->authorize('view', $reconciliation);

        $reconciliation->load([
            'cashier', 'branch', 'session', 'submitter', 'reviewer', 'approver', 'rejector',
        ]);

        $logs = $request->user()?->can('audit', $reconciliation)
            ? $reconciliation->logs()->with('user:id,name')->get()
            : collect();

        return view('admin.commercial.pos.reconciliation.show', [
            'reconciliation' => $reconciliation,
            'logs' => $logs,
            'can_submit' => $request->user()?->can('submit', $reconciliation) ?? false,
            'can_review' => $request->user()?->can('review', $reconciliation) ?? false,
            'can_approve' => $request->user()?->can('approve', $reconciliation) ?? false,
            'can_reject' => $request->user()?->can('reject', $reconciliation) ?? false,
            'can_audit' => $request->user()?->can('audit', $reconciliation) ?? false,
        ]);
    }

    public function submit(Request $request, PosCashReconciliation $reconciliation): RedirectResponse
    {
        $this->authorize('submit', $reconciliation);

        $data = $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->reconciliations->submit(
            reconciliation: $reconciliation,
            userId: (int) $request->user()->id,
            notes: $data['notes'] ?? null,
        );

        return redirect()
            ->route('admin.commercial.pos.reconciliation.show', $reconciliation)
            ->with('status', __('Cash reconciliation submitted for review.'));
    }

    public function review(Request $request, PosCashReconciliation $reconciliation): RedirectResponse
    {
        $this->authorize('review', $reconciliation);

        $data = $request->validate([
            'review_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->reconciliations->review(
            reconciliation: $reconciliation,
            userId: (int) $request->user()->id,
            notes: $data['review_notes'] ?? null,
        );

        return redirect()
            ->route('admin.commercial.pos.reconciliation.show', $reconciliation)
            ->with('status', __('Reconciliation reviewed. Awaiting manager approval.'));
    }

    public function approve(Request $request, PosCashReconciliation $reconciliation): RedirectResponse
    {
        $this->authorize('approve', $reconciliation);

        $data = $request->validate([
            'approval_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->reconciliations->approve(
            reconciliation: $reconciliation,
            userId: (int) $request->user()->id,
            notes: $data['approval_notes'] ?? null,
        );

        return redirect()
            ->route('admin.commercial.pos.reconciliation.show', $reconciliation)
            ->with('status', __('Cash reconciliation approved.'));
    }

    public function reject(Request $request, PosCashReconciliation $reconciliation): RedirectResponse
    {
        $this->authorize('reject', $reconciliation);

        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $this->reconciliations->reject(
            reconciliation: $reconciliation,
            userId: (int) $request->user()->id,
            reason: $data['rejection_reason'],
        );

        return redirect()
            ->route('admin.commercial.pos.reconciliation.show', $reconciliation)
            ->with('status', __('Cash reconciliation rejected.'));
    }
}
