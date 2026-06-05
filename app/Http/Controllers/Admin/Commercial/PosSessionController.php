<?php

namespace App\Http\Controllers\Admin\Commercial;

use App\Enums\PosSessionStatus;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Admin\Crm\Concerns\ResolvesCrmTenant;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Branch;
use App\Models\Pos\PosSession;
use App\Models\User;
use App\Support\Commercial\PosCashReconciliationService;
use App\Support\Commercial\PosSessionReadiness;
use App\Support\Commercial\PosSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class PosSessionController extends Controller
{
    use ResolvesCrmTenant, ScopesToTenant;

    public function __construct(
        protected PosSessionService $sessions,
        protected PosSessionReadiness $readiness,
        protected PosCashReconciliationService $reconciliations,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', PosSession::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds($request);
        $user = $request->user();

        $query = PosSession::query()
            ->with(['cashier:id,name', 'branch:id,name'])
            ->where('company_id', $companyId);

        if (! $user?->can('commercial.pos.sessions.admin')) {
            $query->where('branch_id', $branchId);
        } elseif ($request->filled('branch_id')) {
            $query->where('branch_id', (int) $request->input('branch_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('cashier_id')) {
            $query->where('cashier_id', (int) $request->input('cashier_id'));
        }

        $sessions = $query
            ->orderByDesc('opened_at')
            ->paginate(20)
            ->withQueryString();

        $stats = $this->sessions->dashboardStats(
            $companyId,
            $user?->can('commercial.pos.sessions.admin') && $request->filled('branch_id')
                ? (int) $request->input('branch_id')
                : ($user?->can('commercial.pos.sessions.admin') ? null : $branchId),
        );

        $branches = $user?->can('commercial.pos.sessions.admin')
            ? Branch::query()->where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get(['id', 'name'])
            : collect();

        $cashiers = User::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.commercial.pos.sessions.index', [
            'sessions' => $sessions,
            'stats' => $stats,
            'filters' => $request->only(['status', 'branch_id', 'cashier_id']),
            'branches' => $branches,
            'cashiers' => $cashiers,
            'readiness' => $this->readiness->assess(),
            'report_ready' => $this->readiness->isReady(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('open', PosSession::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds($request);

        $cashiers = User::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $activeSession = $this->sessions->activeSessionForCashier(
            $companyId,
            $branchId,
            (int) $request->user()->id,
        );

        return view('admin.commercial.pos.sessions.create', [
            'cashiers' => $cashiers,
            'defaultCashierId' => (int) $request->user()->id,
            'activeSession' => $activeSession,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('open', PosSession::class);

        $data = $request->validate([
            'cashier_id' => ['required', 'integer', 'exists:users,id'],
            'opening_float' => ['required', 'numeric', 'min:0'],
            'opening_cash' => ['required', 'numeric', 'min:0'],
            'opening_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds($request);

        $session = $this->sessions->openSession(
            companyId: $companyId,
            branchId: $branchId,
            cashierId: (int) $data['cashier_id'],
            openingFloat: (float) $data['opening_float'],
            openingCash: (float) $data['opening_cash'],
            openedBy: (int) $request->user()->id,
            notes: $data['opening_notes'] ?? null,
        );

        return redirect()
            ->route('admin.commercial.pos.sessions.show', $session)
            ->with('status', __('POS session :number opened.', ['number' => $session->session_number]));
    }

    public function show(Request $request, PosSession $session): View
    {
        $this->authorize('view', $session);

        $session->load(['cashier', 'branch', 'opener', 'closer']);
        $metrics = $this->sessions->sessionMetrics($session);

        $sales = $session->sales()
            ->with(['customer:id,company_name', 'payments'])
            ->orderByDesc('sale_date')
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        $canAudit = $request->user()?->can('audit', $session) ?? false;

        $auditTrail = $canAudit
            ? ActivityLog::query()
                ->where('company_id', $session->company_id)
                ->where('model_type', PosSession::class)
                ->where('model_id', $session->id)
                ->latest()
                ->limit(25)
                ->get()
            : collect();

        $governance = $this->sessions->closureGovernance($session);

        return view('admin.commercial.pos.sessions.show', [
            'session' => $session,
            'metrics' => $metrics,
            'sales' => $sales,
            'auditTrail' => $auditTrail,
            'governance' => $governance,
            'can_close' => $request->user()?->can('close', $session)
                && $session->status === PosSessionStatus::Open
                && $governance['can_close'],
            'can_audit' => $canAudit,
        ]);
    }

    public function closeForm(PosSession $session): View
    {
        $this->authorize('close', $session);

        abort_unless($session->status === PosSessionStatus::Open, 404);

        $metrics = $this->sessions->sessionMetrics($session);
        $governance = $this->sessions->closureGovernance($session);

        return view('admin.commercial.pos.sessions.close', [
            'session' => $session->load(['cashier', 'branch']),
            'expectedCash' => $metrics['expected_closing_cash'],
            'metrics' => $metrics,
            'governance' => $governance,
        ]);
    }

    public function close(Request $request, PosSession $session): RedirectResponse
    {
        $this->authorize('close', $session);

        $data = $request->validate([
            'actual_cash' => ['required', 'numeric', 'min:0'],
            'closing_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $closed = $this->sessions->closeSession(
            session: $session,
            actualCash: (float) $data['actual_cash'],
            closedBy: (int) $request->user()->id,
            notes: $data['closing_notes'] ?? null,
        );

        if (Schema::hasTable('pos_cash_reconciliations')) {
            $this->reconciliations->createFromSession($closed, (int) $request->user()->id);
        }

        return redirect()
            ->route('admin.commercial.pos.sessions.show', $closed)
            ->with('status', __('POS session :number closed.', ['number' => $closed->session_number]));
    }
}
