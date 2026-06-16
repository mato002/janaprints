<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ResolvesSettingsScope;
use App\Http\Controllers\Controller;
use App\Models\Platform\SettingsGovernance;
use App\Services\EmailIdentity\CompanyEmailService;
use App\Services\EmailIdentity\CpanelApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompanyEmailController extends Controller
{
    use ResolvesSettingsScope;

    public function __construct(
        protected CompanyEmailService $companyEmail,
        protected CpanelApiClient $cpanel,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', SettingsGovernance::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->resolveSettingsScope($request);
        $connection = $this->cpanel->connectionSummary();
        $mailboxes = [];
        $loadError = null;

        if ($connection['configured']) {
            try {
                $mailboxes = $this->companyEmail->listMailboxes();
            } catch (\Throwable $exception) {
                report($exception);
                $loadError = $exception->getMessage();
            }
        }

        return view('admin.settings.company-email.index', [
            'companyId' => $companyId,
            'branchId' => $branchId,
            'connection' => $connection,
            'mailboxes' => $mailboxes,
            'loadError' => $loadError,
            'defaultQuotaMb' => (int) config('mailboxes.cpanel.default_quota_mb', 250),
            'canManage' => auth()->user()->can('update', new SettingsGovernance()),
            'companies' => $this->companiesForSettingsUser(),
            'branches' => $this->branchesForSettingsCompany($companyId),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('update', new SettingsGovernance());

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->resolveSettingsScope($request);

        return view('admin.settings.company-email.create', [
            'companyId' => $companyId,
            'branchId' => $branchId,
            'connection' => $this->cpanel->connectionSummary(),
            'defaultQuotaMb' => (int) config('mailboxes.cpanel.default_quota_mb', 250),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('update', new SettingsGovernance());

        $validated = $request->validate([
            'local_part' => ['required', 'string', 'max:64', 'regex:/^[a-zA-Z0-9][a-zA-Z0-9._-]*$/'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'quota_mb' => ['nullable', 'integer', 'min:0', 'max:10240'],
        ]);

        try {
            $mailbox = $this->companyEmail->createMailbox(
                $validated['local_part'],
                $validated['password'],
                isset($validated['quota_mb']) ? (int) $validated['quota_mb'] : null,
            );
        } catch (\Throwable $exception) {
            report($exception);

            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['local_part' => $exception->getMessage()]);
        }

        return redirect()
            ->route('admin.settings.company-email.index', $this->scopeQuery($request))
            ->with('status', __('Mailbox :email was created successfully.', ['email' => $mailbox['email']]));
    }

    public function show(Request $request): View
    {
        $this->authorize('viewAny', SettingsGovernance::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->resolveSettingsScope($request);

        $validated = $request->validate([
            'address' => ['required', 'email'],
        ]);

        $mailbox = $this->companyEmail->findMailbox($validated['address']);

        abort_if($mailbox === null, 404);

        return view('admin.settings.company-email.show', [
            'companyId' => $companyId,
            'branchId' => $branchId,
            'mailbox' => $mailbox,
            'connection' => $this->cpanel->connectionSummary(),
            'canManage' => auth()->user()->can('update', new SettingsGovernance()),
            'companies' => $this->companiesForSettingsUser(),
            'branches' => $this->branchesForSettingsCompany($companyId),
        ]);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $this->authorize('update', new SettingsGovernance());

        $validated = $request->validate([
            'address' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        try {
            $this->companyEmail->updatePassword($validated['address'], $validated['password']);
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withErrors(['password' => $exception->getMessage()]);
        }

        return back()->with('status', __('Password updated for :email.', ['email' => $validated['address']]));
    }

    public function updateQuota(Request $request): RedirectResponse
    {
        $this->authorize('update', new SettingsGovernance());

        $validated = $request->validate([
            'address' => ['required', 'email'],
            'quota_mb' => ['nullable', 'integer', 'min:0', 'max:10240'],
            'unlimited_quota' => ['nullable', 'boolean'],
        ]);

        $unlimited = $request->boolean('unlimited_quota');
        $quotaMb = $unlimited ? 0 : (int) ($validated['quota_mb'] ?? 0);

        if (! $unlimited && $quotaMb < 1) {
            return back()->withErrors([
                'quota_mb' => __('Enter a quota of at least 1 MB, or choose unlimited storage.'),
            ]);
        }

        try {
            $this->companyEmail->updateQuota($validated['address'], $quotaMb);
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withErrors(['quota_mb' => $exception->getMessage()]);
        }

        $message = $unlimited
            ? __('Quota set to unlimited for :email.', ['email' => $validated['address']])
            : __('Quota updated to :quota MB for :email.', [
                'email' => $validated['address'],
                'quota' => number_format($quotaMb),
            ]);

        return redirect()
            ->route('admin.settings.company-email.show', ['address' => $validated['address']] + $this->scopeQuery($request))
            ->with('status', $message);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $this->authorize('update', new SettingsGovernance());

        $validated = $request->validate([
            'address' => ['required', 'email'],
        ]);

        try {
            $this->companyEmail->deleteMailbox($validated['address']);
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withErrors(['address' => $exception->getMessage()]);
        }

        return redirect()
            ->route('admin.settings.company-email.index', $this->scopeQuery($request))
            ->with('status', __('Mailbox :email was deleted.', ['email' => $validated['address']]));
    }

    public function testConnection(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', SettingsGovernance::class);

        $result = $this->companyEmail->testConnection();

        return back()->with(
            $result['success'] ? 'status' : 'error',
            $result['message'],
        );
    }

    /**
     * @return array<string, int|null>
     */
    protected function scopeQuery(Request $request): array
    {
        ['companyId' => $companyId, 'branchId' => $branchId] = $this->resolveSettingsScope($request);

        return array_filter([
            'company_id' => $companyId,
            'branch_id' => $branchId,
        ]);
    }
}
